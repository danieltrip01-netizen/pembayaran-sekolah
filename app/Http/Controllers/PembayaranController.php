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

        $pembayaran = Pembayaran::with(['siswa', 'user', 'pembayaranBulan', 'siswaKelas.kelas'])
            // ── Batasi hanya transaksi tahun pelajaran aktif ─────────────────
            ->when(
                $tahunPelajaran,
                fn($q) => $q->where('tahun_pelajaran_id', $tahunPelajaran->id),
                // Jika tidak ada tahun aktif, jangan tampilkan data apapun
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
            // Filter jenjang manual hanya berlaku untuk pengguna tanpa jenjang
            ->when($request->filled('jenjang') && !$jenjang,
                fn($q) => $q->whereHas('siswa', fn($sq) => $sq->where('jenjang', $request->jenjang))
            )
            ->when($request->status_setor === 'belum', fn($q) => $q->whereNull('setoran_id'))
            ->when($request->status_setor === 'sudah', fn($q) => $q->whereNotNull('setoran_id'))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('pembayaran.index', compact('pembayaran', 'tahunPelajaran'));
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

        return view('pembayaran.create', compact('daftarSiswa', 'siswa', 'tahunAjaran', 'tahunPelajaran'));
    }

    // ─── Store ───────────────────────────────────────────────────────────────

    public function store(StorePembayaranRequest $request): RedirectResponse
    {
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($data): RedirectResponse {

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
                        keterangan   : "Kredit dipakai untuk {$pembayaran->kode_bayar}",
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

                return redirect()->route('pembayaran.show', $pembayaran)
                    ->with('success', $pesan);
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

        $pembayaran->load('siswa');
        return view('pembayaran.edit', compact('pembayaran'));
    }

    // ─── Update ──────────────────────────────────────────────────────────────

    public function update(Request $request, Pembayaran $pembayaran): RedirectResponse
    {
        if ($pembayaran->setoran_id) {
            return back()->with('error', 'Pembayaran yang sudah masuk setoran tidak dapat diedit.');
        }

        $validated = $request->validate([
            'tanggal_bayar'   => ['required', 'date'],
            'nominal_donator' => ['nullable', 'numeric', 'min:0'],
            'keterangan'      => ['nullable', 'string', 'max:255'],
        ]);

        $jumlahBulan     = $pembayaran->jumlah_bulan;
        $spp             = (float) $pembayaran->nominal_per_bulan;
        $donaturPerBulan = isset($validated['nominal_donator'])
            ? (float) $validated['nominal_donator']
            : (float) ($pembayaran->nominal_donator / max($jumlahBulan, 1));
        $maminPerBulan   = $jumlahBulan > 0
            ? (float) $pembayaran->nominal_mamin / $jumlahBulan
            : 0.0;

        // Rumus: (SPP − Donatur + Mamin) × jumlah_bulan − kredit
        $tagiBruto  = ($spp - $donaturPerBulan + $maminPerBulan) * $jumlahBulan;
        $kredit     = (float) ($pembayaran->kredit_digunakan ?? 0);
        $totalBayar = max(0.0, $tagiBruto - $kredit);

        $pembayaran->update([
            'tanggal_bayar'   => $validated['tanggal_bayar'],
            'nominal_donator' => $donaturPerBulan * $jumlahBulan,
            'total_bayar'     => $totalBayar,
            'keterangan'      => $validated['keterangan'] ?? null,
        ]);

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