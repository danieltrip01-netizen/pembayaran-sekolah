<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePembayaranRequest;
use App\Models\Pembayaran;
use App\Models\PembayaranBulan;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Traits\HasBulanLabel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\NotifikasiPembayaran;
use Illuminate\Validation\ValidationException;

class PembayaranController extends Controller
{
    use HasBulanLabel;
// ─── Index ───────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $jenjang        = Auth::user()->jenjang;
        $tahunPelajaran = TahunPelajaran::aktif();

        // ── Base query (dipakai dua kali: summary + paginate) ────────────────
        $baseQuery = Pembayaran::query()
            ->when(
                $tahunPelajaran,
                fn($q) => $q->where('tahun_pelajaran_id', $tahunPelajaran->id),
                fn($q) => $q->whereRaw('0 = 1')
            )
            ->when($jenjang, fn($q) =>
                $q->whereHas('siswa', fn($sq) => $sq->where('jenjang', $jenjang))
            )
            ->when($request->filled('tanggal_dari'),
                fn($q) => $q->whereDate('tanggal_bayar', '>=', $request->tanggal_dari)
            )
            ->when($request->filled('tanggal_sampai'),
                fn($q) => $q->whereDate('tanggal_bayar', '<=', $request->tanggal_sampai)
            )
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(fn($inner) =>
                    $inner->whereHas('siswa', fn($sq) => $sq->where('nama', 'like', "%{$search}%"))
                          ->orWhere('kode_bayar', 'like', "%{$search}%")
                );
            })
            ->when($request->filled('jenjang') && !$jenjang,
                fn($q) => $q->whereHas('siswa', fn($sq) => $sq->where('jenjang', $request->jenjang))
            )
            ->when($request->status_setor === 'belum', fn($q) => $q->whereNull('setoran_id'))
            ->when($request->status_setor === 'sudah', fn($q) => $q->whereNotNull('setoran_id'));

        // ── Aggregate dari seluruh hasil filter (1 query) ────────────────────
        $summary = (clone $baseQuery)->selectRaw('
            COUNT(*)                                                 AS total_transaksi,
            COALESCE(SUM(total_bayar), 0)                           AS total_rupiah,
            SUM(CASE WHEN setoran_id IS NULL     THEN 1 ELSE 0 END) AS belum_disetor,
            SUM(CASE WHEN setoran_id IS NOT NULL THEN 1 ELSE 0 END) AS sudah_disetor
        ')->first();

        // ── Paginate dengan eager-load ───────────────────────────────────────
        $pembayaran = (clone $baseQuery)
            ->with(['siswa', 'user', 'pembayaranBulan', 'siswaKelas.kelas'])
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('pembayaran.index', compact('pembayaran', 'summary', 'tahunPelajaran'));
    }
    
    // ─── Create ──────────────────────────────────────────────────────────────

    public function create(Request $request)
    {
        $jenjang        = Auth::user()->jenjang;
        $tahunPelajaran = TahunPelajaran::aktif();

        // Hanya tampilkan siswa yang terdaftar di siswa_kelas tahun pelajaran aktif.
        // Jika tidak ada tahun aktif, kembalikan koleksi kosong agar tidak menyesatkan.
        $daftarSiswa = $tahunPelajaran
            ? Siswa::aktif()
                ->when($jenjang, fn($q) => $q->jenjang($jenjang))
                ->whereHas('siswaKelas', fn($q) =>
                    $q->where('tahun_pelajaran_id', $tahunPelajaran->id)
                )
                ->orderBy('jenjang')
                ->orderBy('nama')
                ->get()
            : collect();

        // Preselect dari query string — pastikan siswa tersebut ada di daftar valid
        $siswa = $request->filled('siswa_id')
            ? $daftarSiswa->firstWhere('id', $request->siswa_id)
            : null;

        $tahunAjaran = $this->getTahunAjaran($tahunPelajaran);

        return view('pembayaran.create', compact('daftarSiswa', 'siswa', 'tahunAjaran', 'tahunPelajaran', 'jenjang'));
    }

    // ─── Store ───────────────────────────────────────────────────────────────

    public function store(StorePembayaranRequest $request): RedirectResponse
    {
        $data      = $request->validated();
        $afterSave = $request->input('after_save', 'show'); // 'show' | 'continue'

        try {
            return DB::transaction(function () use ($data, $afterSave): RedirectResponse {

                // 1. Lock siswa untuk mencegah race condition pada saldo kredit
                $siswa = Siswa::lockForUpdate()->findOrFail($data['siswa_id']);

                // 2. Pastikan ada tahun pelajaran aktif
                $tahunPelajaran = TahunPelajaran::aktif()
                    ?? throw new \RuntimeException(
                        'Tidak ada tahun pelajaran aktif. Aktifkan tahun pelajaran terlebih dahulu.'
                    );

                // 3. Ambil siswa_kelas untuk tahun aktif (sumber nominal SPP, donatur, mamin)
                $ka = $siswa->kelasAktif()->with('kelas')->first()
                    ?? throw new \RuntimeException(
                        "Siswa \"{$siswa->nama}\" belum terdaftar di kelas untuk tahun pelajaran aktif."
                    );

                // 4. Normalisasi & urutkan bulan
                $bulanBaru = array_values(array_unique($data['bulan_bayar']));
                sort($bulanBaru);

                // 5. Cek duplikat bulan
                $bulanSudahBayar = $siswa->getBulanSudahBayar($tahunPelajaran->id);
                $sudahBayar      = array_intersect($bulanBaru, $bulanSudahBayar);

                if (!empty($sudahBayar)) {
                    $label = implode(', ', array_map([static::class, 'formatBulan'], $sudahBayar));
                    throw ValidationException::withMessages([
                        'bulan_bayar' => "Bulan berikut sudah dibayar: {$label}",
                    ]);
                }

                // 6. Hitung tagihan
                $jumlahBulan     = count($bulanBaru);
                $spp             = (float) $ka->nominal_spp;
                $mamin           = $siswa->jenjang === 'TK' ? (float) $ka->nominal_mamin : 0.0;
                $donaturPerBulan = isset($data['nominal_donator'])
                    ? (float) $data['nominal_donator']
                    : (float) $ka->nominal_donator;

                // Rumus: (SPP − Donatur + Mamin) × jumlah_bulan
                $tagiBruto    = ($spp - $donaturPerBulan + $mamin) * $jumlahBulan;
                $kreditDiskon = max(0.0, min((float) $siswa->saldo_kredit, $tagiBruto));
                $totalBayar   = max(0.0, $tagiBruto - $kreditDiskon);

                // 7. Buat record pembayaran
                $pembayaran = Pembayaran::create([
                    'kode_bayar'         => Pembayaran::generateKodeBayar(),
                    'siswa_id'           => $siswa->id,
                    'user_id'            => Auth::id(),
                    'tahun_pelajaran_id' => $tahunPelajaran->id,
                    'siswa_kelas_id'     => $ka->id,
                    'tanggal_bayar'      => $data['tanggal_bayar'],
                    'jumlah_bulan'       => $jumlahBulan,
                    'nominal_per_bulan'  => $spp,
                    // Disimpan sebagai TOTAL (per_bulan × jumlah_bulan)
                    'nominal_mamin'      => $mamin * $jumlahBulan,
                    'nominal_donator'    => $donaturPerBulan * $jumlahBulan,
                    'kredit_digunakan'   => $kreditDiskon,
                    'total_bayar'        => $totalBayar,
                    'sisa_tagihan'       => 0,
                    'status'             => 'lunas',
                    'keterangan'         => $data['keterangan'] ?? null,
                ]);

                // 8. Simpan detail bulan
                PembayaranBulan::insert(
                    array_map(fn($b) => [
                        'pembayaran_id' => $pembayaran->id,
                        'siswa_id'      => $siswa->id,
                        'bulan'         => $b,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ], $bulanBaru)
                );

                // 9. Potong kredit jika ada
                if ($kreditDiskon > 0) {
                    $siswa->pakaiKredit(
                        jumlah       : $kreditDiskon,
                        pembayaranId : $pembayaran->id,
                        keterangan   : "Kredit dipakai untuk:",
                    );
                }

                $pesan = "Pembayaran berhasil disimpan. Kode: {$pembayaran->kode_bayar}";
                if ($kreditDiskon > 0) {
                    $pesan .= '. Kredit Rp ' . number_format($kreditDiskon, 0, ',', '.')
                            . ' telah dipotongkan dari tagihan.';
                }

                // ── Notifikasi WhatsApp ──────────────────────────────────────
                // Dibungkus try/catch agar kegagalan WA tidak rollback transaksi.
                try {
                    app(NotifikasiPembayaran::class)->kirimSetelahBayar($pembayaran);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning(
                        'Notifikasi WA gagal: ' . $e->getMessage()
                    );
                }

                // ── Redirect sesuai tombol yang ditekan ─────────────────────
                if ($afterSave === 'continue') {
                    // Simpan & Lanjut: kembali ke form baru, pertahankan tanggal
                    $tanggal = $data['tanggal_bayar'];
                    $flash   = "✓ <strong>{$pembayaran->kode_bayar}</strong> — {$siswa->nama} berhasil disimpan.";
                    if ($kreditDiskon > 0) {
                        $flash .= ' Kredit Rp ' . number_format($kreditDiskon, 0, ',', '.') . ' dipotong.';
                    }

                    return redirect()
                        ->route('pembayaran.create', ['tanggal_bayar' => $tanggal])
                        ->with('lanjut_success', $flash);
                }

                return redirect()->route('pembayaran.show', $pembayaran)
                    ->with('success', $pesan)
                    ->with('auto_redirect', true);
            });

        } catch (\RuntimeException $e) {
            return redirect()->route('pembayaran.create')
                ->with('error', $e->getMessage());
        }
    }

    // ─── Show ────────────────────────────────────────────────────────────────

    public function show(Pembayaran $pembayaran)
    {
        $pembayaran->load(['siswa', 'user', 'setoran', 'kreditLog.user', 'pembayaranBulan']);
        return view('pembayaran.show', compact('pembayaran'));
    }

// ─── Edit ────────────────────────────────────────────────────────────────

    public function edit(Pembayaran $pembayaran)
    {
        if ($pembayaran->setoran_id) {
            return redirect()->route('pembayaran.show', $pembayaran)
                ->with('error', 'Pembayaran yang sudah masuk setoran tidak dapat diedit.');
        }

        $pembayaran->load(['siswa.kelasAktif.kelas', 'pembayaranBulan']);

        $siswa          = $pembayaran->siswa;
        $tahunPelajaran = TahunPelajaran::aktif();
        $tahunAjaran    = $this->getTahunAjaran($tahunPelajaran);

        // Periode yang dimiliki pembayaran ini → pre-selected di grid
        $bulanTerpilih = $pembayaran->pembayaranBulan->pluck('bulan')->toArray();

        // Semua periode aktif untuk siswa ini (12 bulan tahun pelajaran)
        $bulanAktif = $siswa->getBulanAktif($tahunAjaran);

        // Semua periode yang sudah dibayar siswa ini KECUALI pembayaran ini
        $bulanDibayarLain = PembayaranBulan::where('siswa_id', $siswa->id)
            ->whereHas('pembayaran', fn($q) => $q->where('id', '!=', $pembayaran->id))
            ->pluck('bulan')
            ->toArray();

        // Nominal per-bulan (snapshot lama, hanya untuk preview ringkasan)
        $donaturPerBln = $pembayaran->jumlah_bulan > 0
            ? (int) round($pembayaran->nominal_donator / $pembayaran->jumlah_bulan)
            : 0;
        $maminPerBln = $pembayaran->jumlah_bulan > 0
            ? (int) round($pembayaran->nominal_mamin / $pembayaran->jumlah_bulan)
            : 0;

        return view('pembayaran.edit', compact(
            'pembayaran',
            'tahunAjaran',
            'bulanTerpilih',
            'bulanDibayarLain',
            'bulanAktif',
            'donaturPerBln',
            'maminPerBln',
        ));
    }

    // ─── Update ──────────────────────────────────────────────────────────────

    public function update(Request $request, Pembayaran $pembayaran): RedirectResponse
    {
        if ($pembayaran->setoran_id) {
            return back()->with('error', 'Pembayaran yang sudah masuk setoran tidak dapat diedit.');
        }

        $validated = $request->validate([
            'tanggal_bayar' => ['required', 'date'],
            'bulan_bayar'   => ['required', 'array', 'min:1'],
            'bulan_bayar.*' => ['string', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'keterangan'    => ['nullable', 'string', 'max:255'],
        ]);

        $bulanBaru = array_values(array_unique($validated['bulan_bayar']));
        sort($bulanBaru);

        // Cek bentrok: bulan yang sudah diklaim pembayaran lain siswa yang sama
        $bentrok = PembayaranBulan::whereIn('bulan', $bulanBaru)
            ->where('siswa_id', $pembayaran->siswa_id)
            ->whereHas('pembayaran', fn($q) => $q->where('id', '!=', $pembayaran->id))
            ->pluck('bulan')
            ->toArray();

        if (!empty($bentrok)) {
            $label = implode(', ', array_map([static::class, 'formatBulan'], $bentrok));
            return back()
                ->withInput()
                ->withErrors(['bulan_bayar' => "Bulan berikut sudah dibayar di transaksi lain: {$label}"]);
        }

        DB::transaction(function () use ($pembayaran, $validated, $bulanBaru): void {
            $jumlahBulan = count($bulanBaru);
            $spp         = (float) $pembayaran->nominal_per_bulan;

            // Donatur & mamin diambil dari snapshot pembayaran lama — tidak boleh diubah di sini
            $donaturPerBulan = $pembayaran->jumlah_bulan > 0
                ? (float) $pembayaran->nominal_donator / $pembayaran->jumlah_bulan
                : 0.0;
            $maminPerBulan = $pembayaran->jumlah_bulan > 0
                ? (float) $pembayaran->nominal_mamin / $pembayaran->jumlah_bulan
                : 0.0;

            // Hitung ulang total berdasarkan jumlah bulan baru
            $tagiBruto  = ($spp - $donaturPerBulan + $maminPerBulan) * $jumlahBulan;
            $kredit     = (float) ($pembayaran->kredit_digunakan ?? 0);
            $totalBayar = max(0.0, $tagiBruto - $kredit);

            // Ganti detail bulan: hapus lama, insert baru
            $pembayaran->pembayaranBulan()->delete();

            PembayaranBulan::insert(
                array_map(fn($b) => [
                    'pembayaran_id' => $pembayaran->id,
                    'siswa_id'      => $pembayaran->siswa_id,
                    'bulan'         => $b,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ], $bulanBaru)
            );

            $pembayaran->update([
                'tanggal_bayar'  => $validated['tanggal_bayar'],
                'jumlah_bulan'   => $jumlahBulan,
                'nominal_donator'=> $donaturPerBulan * $jumlahBulan,
                'nominal_mamin'  => $maminPerBulan   * $jumlahBulan,
                'total_bayar'    => $totalBayar,
                'keterangan'     => $validated['keterangan'] ?? null,
            ]);
        });

        return redirect()->route('pembayaran.show', $pembayaran)
            ->with('success', 'Data pembayaran berhasil diperbarui.');
    }

    // ─── Destroy ─────────────────────────────────────────────────────────────

    public function destroy(Pembayaran $pembayaran): RedirectResponse
    {
        if ($pembayaran->setoran_id) {
            return back()->with('error', 'Pembayaran ini sudah masuk setoran. Hapus setoran terlebih dahulu.');
        }

        DB::transaction(function () use ($pembayaran): void {
            $kreditKembali = (float) ($pembayaran->kredit_digunakan ?? 0);

            if ($kreditKembali > 0) {
                $pembayaran->siswa->tambahKredit(
                    jumlah     : $kreditKembali,
                    keterangan : "Kredit dikembalikan karena pembayaran {$pembayaran->kode_bayar} dihapus",
                );
            }

            // Hapus detail bulan sebelum soft-delete induknya
            $pembayaran->pembayaranBulan()->delete();
            $pembayaran->delete();
        });

        return redirect()->route('pembayaran.index')
            ->with('success', "Pembayaran {$pembayaran->kode_bayar} berhasil dihapus.");
    }

    // ─── AJAX: Data Siswa ────────────────────────────────────────────────────

    /**
     * GET /siswa/{siswa}/data
     * Dipanggil JS di form create untuk memuat info siswa, nominal, dan status bulan.
     */
    public function getSiswaData(Siswa $siswa)
    {
        $userJenjang = Auth::user()->jenjang;

        if ($userJenjang && $siswa->jenjang !== $userJenjang) {
            return response()->json(['message' => 'Akses tidak diizinkan.'], 403);
        }

        $siswa->load('kelasAktif.kelas');

        $ka             = $siswa->kelasAktif;
        $tahunPelajaran = TahunPelajaran::aktif();
        $tahunAjaran    = $this->getTahunAjaran($tahunPelajaran);
        $bulanAktif     = $siswa->getBulanAktif($tahunAjaran);
        $bulanDibayar   = $siswa->getBulanSudahBayar($tahunPelajaran?->id);
        $bulanBelum     = array_values(array_diff($bulanAktif, $bulanDibayar));

        return response()->json([
            'siswa' => array_merge($siswa->toArray(), [
                // Nama field sesuai ekspektasi JS di form create
                'nominal_pembayaran' => (int) ($ka?->nominal_spp     ?? 0),
                'nominal_donator'    => (int) ($ka?->nominal_donator ?? 0),
                'nominal_mamin'      => (int) ($ka?->nominal_mamin   ?? 0),
                'kelas'              => $ka?->kelas?->nama ?? '',
            ]),
            'bulan_aktif'   => $bulanAktif,
            'bulan_dibayar' => $bulanDibayar,
            'bulan_belum'   => $bulanBelum,
            'saldo_kredit'  => (int) $siswa->saldo_kredit,
        ]);
    }

    // ─── AJAX: Preview Kalkulasi ─────────────────────────────────────────────

    /**
     * GET /pembayaran/preview
     * Kalkulasi tagihan real-time dari JS (opsional).
     */
    public function preview(Request $request)
    {
        $siswa = Siswa::with('kelasAktif')->findOrFail($request->integer('siswa_id'));
        $ka    = $siswa->kelasAktif;

        $jumlah          = max(1, (int) $request->input('jumlah_bulan', 1));
        $spp             = (float) ($ka?->nominal_spp ?? 0);
        $mamin           = $siswa->jenjang === 'TK' ? (float) ($ka?->nominal_mamin ?? 0) : 0.0;
        $donaturPerBulan = (float) $request->input('nominal_donator', $ka?->nominal_donator ?? 0);

        $tagiBruto    = ($spp - $donaturPerBulan + $mamin) * $jumlah;
        $kreditDiskon = max(0.0, min((float) $siswa->saldo_kredit, $tagiBruto));

        return response()->json([
            'tagihan_bruto'    => $tagiBruto,
            'kredit_digunakan' => $kreditDiskon,
            'total_bayar'      => max(0.0, $tagiBruto - $kreditDiskon),
        ]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Tahun awal tahun pelajaran aktif.
     * Terima instance agar tidak memanggil DB dua kali.
     */
    private function getTahunAjaran(?TahunPelajaran $tahunPelajaran = null): int
    {
        if ($tahunPelajaran) {
            return (int) $tahunPelajaran->tanggal_mulai->format('Y');
        }

        // Fallback: tahun ajaran dimulai Juli
        return (int) date('m') >= 7 ? (int) date('Y') : (int) date('Y') - 1;
    }

}