<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\SiswaKelas;
use App\Models\TahunPelajaran;
use App\Http\Requests\StoreSiswaRequest;
use App\Traits\HasBulanLabel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SiswaController extends Controller
{
    use HasBulanLabel;
    public function index(Request $request)
    {
        $user    = Auth::user();
        $jenjang = $user->jenjang; // null = admin yayasan (lihat semua)

        // Ambil tahun pelajaran aktif untuk label di header tabel
        $tahunPelajaran = TahunPelajaran::aktif();

        $query = Siswa::query()
            ->with(['kelasAktif.kelas'])
            // Hanya tampilkan siswa yang terdaftar di tahun pelajaran aktif
            ->whereHas('kelasAktif');

        // Filter jenjang dari role user
        if ($jenjang) {
            $query->where('jenjang', $jenjang);
        }
        if ($request->filled('jenjang') && !$jenjang) {
            $query->where('jenjang', $request->jenjang);
        }

        // Filter kelas — via siswa_kelas + kelas
        if ($request->filled('kelas_id')) {
            $query->whereHas('kelasAktif', fn($q) =>
                $q->where('kelas_id', $request->kelas_id)
            );
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('id_siswa', 'like', '%' . $request->search . '%');
            });
        }

        $siswa = $query
            ->withCount('pembayaran')
            ->orderBy('jenjang')
            ->orderBy('nama')
            ->paginate(20)
            ->withQueryString();

        // Daftar kelas untuk dropdown filter (sesuai jenjang user)
        $kelasOptions = Kelas::query()
            ->when($jenjang, fn($q) => $q->where('jenjang', $jenjang))
            ->when($request->filled('jenjang') && !$jenjang, fn($q) =>
                $q->where('jenjang', $request->jenjang)
            )
            ->terurut()
            ->get();

        $jenjangOptions = $jenjang ? [$jenjang] : ['TK', 'SD', 'SMP'];

        return view('siswa.index', compact('siswa', 'jenjangOptions', 'jenjang', 'kelasOptions', 'tahunPelajaran'));
    }

    public function create()
    {
        $userJenjang    = Auth::user()->jenjang ?? null;
        $jenjang        = $userJenjang ?? 'SD';
        $idSiswa        = Siswa::generateIdSiswa($jenjang);
        $tahunPelajaran = TahunPelajaran::aktif();
        $semuaKelas     = $this->buildSemuaKelas();

        return view('siswa.create', compact(
            'jenjang', 'idSiswa', 'tahunPelajaran', 'semuaKelas', 'userJenjang'
        ));
    }

    public function store(StoreSiswaRequest $request)
    {
        $validated = $request->validated();

        $dataSiswa = [
            // BUG FIX #1 lanjutan: setelah id_siswa ditambahkan ke rules,
            // $validated['id_siswa'] bisa berisi nilai dari form.
            // Jika kosong/null, baru fallback ke auto-generate.
            'id_siswa'       => ($validated['id_siswa'] ?? '') !== ''
                                    ? $validated['id_siswa']
                                    : Siswa::generateIdSiswa($validated['jenjang']),
            'nama'           => $validated['nama'],
            'jenjang'        => $validated['jenjang'],
            'tanggal_masuk'  => $validated['tanggal_masuk'] ?? date('Y') . '-07-01',
            'tanggal_keluar' => $validated['tanggal_keluar'] ?? null,
            'status'         => $validated['status'],
            'keterangan'     => $validated['keterangan'] ?? null,
            'no_hp_wali'     => $validated['no_hp_wali'] ?? null,
            'saldo_kredit'   => 0,
        ];

        $siswa = DB::transaction(function () use ($dataSiswa, $validated) {
            $siswa = Siswa::create($dataSiswa);

            // Buat record siswa_kelas hanya jika kedua field tersedia
            if (!empty($validated['kelas_id']) && !empty($validated['tahun_pelajaran_id'])) {
                SiswaKelas::create([
                    'siswa_id'           => $siswa->id,
                    'kelas_id'           => $validated['kelas_id'],
                    'tahun_pelajaran_id' => $validated['tahun_pelajaran_id'],
                    'nominal_spp'        => $validated['nominal_spp'] ?? 0,
                    'nominal_donator'    => $validated['nominal_donator'] ?? 0,
                    // BUG FIX #4: nominal_mamin dipaksa 0 di server untuk non-TK
                    'nominal_mamin'      => $dataSiswa['jenjang'] === 'TK'
                                            ? ($validated['nominal_mamin'] ?? 0)
                                            : 0,
                ]);
            }

            return $siswa;
        });

        return redirect()->route('siswa.show', $siswa)
                         ->with('success', 'Data siswa "' . $siswa->nama . '" berhasil ditambahkan.');
    }

    public function show(Siswa $siswa)
    {
        $this->authorizeJenjang($siswa);

        $siswa->load([
            'pembayaran' => fn($q) => $q->with(['user', 'pembayaranBulan'])
                                        ->orderBy('tanggal_bayar', 'desc'),
            'kelasAktif.kelas',
            'kelasAktif.tahunPelajaran',
        ]);

        $tahunPelajaran = TahunPelajaran::aktif();
        $tahunAjaran    = $tahunPelajaran?->tahun_ajaran
                            ?? ((int) date('m') >= 7 ? (int) date('Y') : (int) date('Y') - 1);

        $bulanAktif   = $siswa->getBulanAktif($tahunAjaran);
        $bulanDibayar = $this->getBulanDibayar($siswa, $tahunPelajaran?->id);

        $statusBulan = $this->hitungStatusBulan(
            $bulanAktif,
            $bulanDibayar,
            $siswa->pembayaran
        );

        $kelasAktif = $siswa->kelasAktif;

        return view('siswa.show', compact(
            'siswa', 'statusBulan', 'bulanAktif', 'tahunAjaran', 'kelasAktif', 'tahunPelajaran'
        ));
    }

    public function edit(Siswa $siswa)
    {
        $this->authorizeJenjang($siswa);

        $siswa->load(['kelasAktif.kelas', 'kelasAktif.tahunPelajaran']);

        $userJenjang    = Auth::user()->jenjang ?? null;
        $jenjang        = $siswa->jenjang;
        $tahunPelajaran = TahunPelajaran::aktif();
        $semuaKelas     = $this->buildSemuaKelas();

        // Data untuk preview kredit otomatis di blade
        $ka = $siswa->kelasAktif;
        $donaturSekarang   = $ka ? (float) $ka->nominal_donator : 0.0;
        $bulanSudahDibayar = $tahunPelajaran
            ? $this->getBulanDibayar($siswa, $tahunPelajaran->id)
            : [];
        $jumlahBulanDibayar = count($bulanSudahDibayar);

        return view('siswa.edit', compact(
            'siswa', 'semuaKelas', 'tahunPelajaran', 'userJenjang', 'jenjang',
            'donaturSekarang', 'jumlahBulanDibayar', 'bulanSudahDibayar'
        ));
    }

    public function update(StoreSiswaRequest $request, Siswa $siswa)
    {
        $this->authorizeJenjang($siswa);
        $validated = $request->validated();

        $dataSiswa = [
            'nama'           => $validated['nama'],
            'jenjang'        => $validated['jenjang'],
            'tanggal_masuk'  => $validated['tanggal_masuk'],
            'tanggal_keluar' => $validated['tanggal_keluar'] ?? null,
            'status'         => $validated['status'],
            'keterangan'     => $validated['keterangan'] ?? null,
            'no_hp_wali'     => $validated['no_hp_wali'] ?? null,
        ];

        // BUG FIX #3: Sebelumnya kondisi menggunakan !$siswa->tanggal_keluar
        // (nilai LAMA di DB), bukan nilai yang dikirim dari form.
        //
        // Skenario bug yang terjadi:
        //   - status = tidak_aktif
        //   - tanggal_keluar lama di DB = null
        //   - admin mengisi tanggal_keluar baru di form
        //   Hasilnya: $dataSiswa['tanggal_keluar'] pertama diset ke nilai admin,
        //   lalu kondisi lama (!$siswa->tanggal_keluar = true) menimpa dengan now().
        //   → Input tanggal dari admin TERBUANG!
        //
        // Fix yang benar: cek apakah admin mengosongkan tanggal_keluar di form.
        if ($validated['status'] === 'tidak_aktif' && empty($validated['tanggal_keluar'])) {
            $dataSiswa['tanggal_keluar'] = now()->toDateString();
        }

        // Jika status kembali aktif, hapus tanggal keluar
        if ($validated['status'] === 'aktif') {
            $dataSiswa['tanggal_keluar'] = null;
        }

        $kreditLog = null;

        DB::transaction(function () use ($siswa, $dataSiswa, $validated, &$kreditLog) {
            $siswa->update($dataSiswa);

            if (!empty($validated['kelas_id']) && !empty($validated['tahun_pelajaran_id'])) {

                $donaturBaru = (float) ($validated['nominal_donator'] ?? 0);
                $nominalMamin = $dataSiswa['jenjang'] === 'TK'
                    ? (float) ($validated['nominal_mamin'] ?? 0)
                    : 0.0;

                // Ambil record lama SEBELUM updateOrCreate agar bisa bandingkan donatur
                $kaLama = SiswaKelas::where('siswa_id', $siswa->id)
                    ->where('tahun_pelajaran_id', $validated['tahun_pelajaran_id'])
                    ->first();

                $donaturLama = $kaLama ? (float) $kaLama->nominal_donator : 0.0;

                // Simpan / update record siswa_kelas
                $ka = SiswaKelas::updateOrCreate(
                    [
                        'siswa_id'           => $siswa->id,
                        'tahun_pelajaran_id' => $validated['tahun_pelajaran_id'],
                    ],
                    [
                        'kelas_id'        => $validated['kelas_id'],
                        'nominal_spp'     => $validated['nominal_spp'] ?? 0,
                        'nominal_donator' => $donaturBaru,
                        'nominal_mamin'   => $nominalMamin,
                    ]
                );

                // ── Kredit otomatis jika donatur naik & sudah pernah bayar ──
                // Muat relasi siswa ke $ka agar terapkanKreditOtomatis tidak re-query
                $ka->setRelation('siswa', $siswa);
                $kreditLog = $ka->terapkanKreditOtomatis($donaturLama, $donaturBaru);
            }
        });

        // Susun pesan sukses, sertakan info kredit jika ada
        $pesan = 'Data siswa berhasil diperbarui.';
        if ($kreditLog) {
            $pesan .= sprintf(
                ' Kredit otomatis Rp %s ditambahkan ke saldo %s.',
                number_format($kreditLog->jumlah, 0, ',', '.'),
                $siswa->nama
            );
        }

        return redirect()->route('siswa.show', $siswa)->with('success', $pesan);
    }

    public function destroy(Siswa $siswa)
    {
        $this->authorizeJenjang($siswa);
        $nama = $siswa->nama;
        $siswa->delete();

        return redirect()->route('siswa.index')
                         ->with('success', 'Data siswa "' . $nama . '" berhasil dihapus.');
    }

    // ─── Riwayat Pembayaran via QR (public, no auth) ─────────────────────────

    public function riwayatPembayaran(Siswa $siswa, Request $request)
    {
        // Validasi access_token — mencegah akses tanpa QR code yang valid
        if (!$request->filled('token') || !hash_equals((string) $siswa->access_token, (string) $request->query('token'))) {
            abort(403, 'Akses tidak diizinkan.');
        }

        // Load semua relasi yang dibutuhkan sekaligus
        $siswa->load([
            'pembayaran' => fn($q) => $q->with('pembayaranBulan')
                                        ->orderBy('tanggal_bayar'),
            'kelasAktif.kelas',
            'kelasAktif.tahunPelajaran',
        ]);

        // ── Tahun pelajaran aktif ────────────────────────────────────────────
        $tahunPelajaran = TahunPelajaran::aktif();

        $tahunAjaran = $tahunPelajaran?->tahun_ajaran
                        ?? ((int) date('m') >= 7 ? (int) date('Y') : (int) date('Y') - 1);

        $tahunNama = $tahunPelajaran
            ? (string) $tahunPelajaran->nama      // mis. "2024/2025"
            : $tahunAjaran . '/' . ($tahunAjaran + 1);

        // ── Data kelas & nominal ─────────────────────────────────────────────
        $ka         = $siswa->kelasAktif;
        $kelasNama  = $ka?->kelas?->nama ?? '—';
        $nomSpp     = (float) ($ka?->nominal_spp      ?? 0);
        $nomDonatur = (float) ($ka?->nominal_donator  ?? 0);
        $nomMamin   = (float) ($ka?->nominal_mamin    ?? 0);
        $isTK       = strtoupper($siswa->jenjang ?? '') === 'TK';

        // ── Bangun status bulan (reuse helper yang sudah ada) ────────────────
        $bulanAktif   = $siswa->getBulanAktif($tahunAjaran);
        $bulanDibayar = $this->getBulanDibayar($siswa, $tahunPelajaran?->id);
        $statusBulan  = $this->hitungStatusBulan($bulanAktif, $bulanDibayar, $siswa->pembayaran);

        // ── Bangun array riwayat untuk view ─────────────────────────────────
        $riwayat = array_map(function (array $item) use ($nomSpp, $nomDonatur, $nomMamin, $isTK) {
            /** @var \App\Models\Pembayaran|null $bayar */
            $bayar = $item['data_bayar'];

            // Nominal dari record pembayaran (jika sudah bayar) atau dari siswa_kelas
            if ($bayar) {
                $jmlBln         = max(1, (int) $bayar->jumlah_bulan);
                $sppRow         = (float) $bayar->nominal_per_bulan;
                $donaturRow     = round((float) $bayar->nominal_donator  / $jmlBln, 0);
                $maminRow       = round((float) $bayar->nominal_mamin    / $jmlBln, 0);
                $kreditRow      = round((float) ($bayar->kredit_digunakan ?? 0) / $jmlBln, 0);
                $yangDibayarRow = max(0, $sppRow - $donaturRow + $maminRow - $kreditRow);
            } else {
                $sppRow         = $nomSpp;
                $donaturRow     = $nomDonatur;
                $maminRow       = $isTK ? $nomMamin : 0.0;
                $kreditRow      = 0.0;
                $yangDibayarRow = max(0, $sppRow - $donaturRow + $maminRow);
            }

            return [
                'periode'      => $item['bulan'],          // '2024-07'
                'nama_bulan'   => $item['nama_bulan'],      // 'Juli'
                'sudah_bayar'  => $item['sudah_bayar'],
                'tanggal'      => $bayar
                    ? \Carbon\Carbon::parse($bayar->tanggal_bayar)->translatedFormat('d M Y')
                    : null,
                'kode_bayar'   => $bayar?->kode_bayar,
                'spp'          => $sppRow,
                'donatur'      => $donaturRow,
                'mamin'        => $maminRow,
                'kredit'       => $kreditRow,
                'yang_dibayar' => $yangDibayarRow,
            ];
        }, $statusBulan);

        $totalLunas   = count(array_filter($riwayat, fn($r) => $r['sudah_bayar']));
        $totalTagihan = count($riwayat);

        // ── Setting sekolah dari DB ──────────────────────────────────────────
        $jenjang     = $siswa->jenjang ?? 'SD';
        $settingJnj  = \App\Models\Setting::forJenjang($jenjang);
        $settingGlob = \App\Models\Setting::global();

        $namaSekolah  = $settingJnj?->nama_sekolah  ?? $jenjang;
        $namaYayasan  = $settingGlob?->nama_yayasan ?? '';
        $kota         = $settingGlob?->kota         ?? '';

        // Logo sebagai URL publik
        $logoUrl = $settingJnj?->logo
            ? \Illuminate\Support\Facades\Storage::url($settingJnj->logo)
            : null;

        return view('siswa.riwayat-pembayaran', compact(
            'siswa',
            'kelasNama',
            'tahunAjaran',
            'tahunNama',
            'namaSekolah',
            'namaYayasan',
            'kota',
            'logoUrl',
            'riwayat',
            'totalLunas',
            'totalTagihan',
        ));
    }

    // ─── Private Helpers ────────────────────────────────────────────

    private function authorizeJenjang(Siswa $siswa): void
    {
        $user = Auth::user();
        if ($user->jenjang && $user->jenjang !== $siswa->jenjang) {
            abort(403, 'Anda tidak memiliki akses ke data siswa ini.');
        }
    }

    /**
     * Bangun $semuaKelas sebagai plain PHP array agar @json() di blade
     * menghasilkan output yang konsisten dan bisa dibaca JS.
     *
     * Menggunakan plain array (bukan Collection->groupBy()) karena nested Collection
     * kadang tidak ter-encode benar tergantung versi Laravel.
     *
     * Output: ['TK' => [['id'=>1,'nama'=>'KB A'], ...], 'SD' => [...], ...]
     */
    private function buildSemuaKelas(): array
    {
        $result = [];

        Kelas::terurut()->get()->each(function (Kelas $kelas) use (&$result) {
            $result[$kelas->jenjang][] = [
                'id'   => $kelas->id,
                'nama' => $kelas->nama,
            ];
        });

        return $result;
    }

    /**
     * Ambil bulan yang sudah dibayar dari tabel pembayaran_bulan.
     */
    private function getBulanDibayar(Siswa $siswa, ?int $tahunPelajaranId): array
    {
        return $siswa->pembayaranBulan()
            ->when($tahunPelajaranId, fn($q) =>
                $q->whereHas('pembayaran', fn($p) =>
                    $p->where('tahun_pelajaran_id', $tahunPelajaranId)
                )
            )
            ->pluck('bulan')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Bangun array status per bulan untuk tampilan kalender pembayaran.
     */
    private function hitungStatusBulan(array $bulanAktif, array $bulanDibayar, $pembayaranCollection): array
    {
        $status = [];

        foreach ($bulanAktif as $bulan) {
            [, $bln] = explode('-', $bulan);
            $sudahBayar = in_array($bulan, $bulanDibayar, true);
            $dataBayar  = null;

            if ($sudahBayar) {
                foreach ($pembayaranCollection as $p) {
                    if (in_array($bulan, $p->pembayaranBulan->pluck('bulan')->all(), true)) {
                        $dataBayar = $p;
                        break;
                    }
                }
            }

            $status[] = [
                'bulan'       => $bulan,
                'nama_bulan'  => static::$namaBulan[$bln] ?? $bln,
                'sudah_bayar' => $sudahBayar,
                'data_bayar'  => $dataBayar,
            ];
        }

        return $status;
    }
}