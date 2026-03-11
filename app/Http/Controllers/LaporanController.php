<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\TahunPelajaran;
use App\Models\Setting;
use App\Exports\LaporanExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $user        = auth()->user();
        $userJenjang = $user->jenjang; // null = admin yayasan

        // ── Tahun Pelajaran ───────────────────────────────────────────────────
        $tahunAktif         = TahunPelajaran::aktif();
        $tahunPelajaranList = TahunPelajaran::orderByDesc('tanggal_mulai')->get();

        // Default: gunakan tahun pelajaran aktif jika tidak ada request
        $defaultTahunId   = $tahunAktif?->id ?? '';
        $tahunPelajaranId = $request->get('tahun_pelajaran_id', $defaultTahunId);

        $filter = [
            'tahun_pelajaran_id' => $tahunPelajaranId,

            // Admin yayasan → jenjang bisa kosong (tampilkan semua).
            // Admin jenjang  → paksa jenjangnya sendiri, tidak bisa diubah.
            'jenjang'        => $userJenjang ?? $request->get('jenjang', ''),

            // Default bulan KOSONG supaya tampilkan SEMUA data.
            'bulan'          => $request->get('bulan', ''),

            'kelas'          => $request->get('kelas', ''),
            'tanggal_dari'   => $request->get('tanggal_dari', ''),
            'tanggal_sampai' => $request->get('tanggal_sampai', ''),
        ];

        $pembayaran = $this->buildQuery($filter)->get();

        $rekap = $this->hitungRekap($pembayaran);

        // ── perKelas: ambil nama kelas dari relasi siswaKelas ─────────────────
        // Kelas disimpan di tabel siswa_kelas, bukan di kolom siswa.kelas
        $tahunId  = $filter['tahun_pelajaran_id'];
        $perKelas = $pembayaran 
            ->groupBy(fn($p) =>
                $p->siswa
                    ?->siswaKelas
                    ->firstWhere('tahun_pelajaran_id', $tahunId)
                    ?->kelas
                    ?->nama ?? '-'
            )
            ->map(fn($group) => [
                'jumlah_siswa' => $group->pluck('siswa_id')->unique()->count(),
                'total'        => $group->sum('total_bayar'),
            ])
            ->sortKeys();

        // ── filterAktif: label filter yang sedang aktif (untuk tampilan UI) ───
        $filterAktif = [];
        if (!empty($filter['jenjang']))            $filterAktif[] = 'Jenjang: ' . $filter['jenjang'];
        if (!empty($filter['kelas']))              $filterAktif[] = 'Kelas: '   . $filter['kelas'];
        if (!empty($filter['bulan']))              $filterAktif[] = 'Tgl Transaksi: ' . \Carbon\Carbon::createFromFormat('Y-m', $filter['bulan'])->isoFormat('MMMM Y');
        if (!empty($filter['tanggal_dari']))       $filterAktif[] = 'Dari: '    . $filter['tanggal_dari'];
        if (!empty($filter['tanggal_sampai']))     $filterAktif[] = 'Sampai: '  . $filter['tanggal_sampai'];

        // Admin jenjang hanya lihat jenjangnya sendiri; admin yayasan bisa filter semua
        $jenjangOptions = $userJenjang ? [$userJenjang] : ['TK', 'SD', 'SMP'];

        // ── Opsi kelas berdasarkan jenjang yang aktif / dipilih ───────────────
        // Jika jenjang sudah terkunci (admin jenjang) atau dipilih via filter,
        // tampilkan hanya kelas yang relevan. Jika semua jenjang → tampilkan semua.
        $kelasByJenjang = [
            'TK'  => ['A', 'B'],
            'SD'  => ['I', 'II', 'III', 'IV', 'V', 'VI'],
            'SMP' => ['VII', 'VIII', 'IX'],
        ];

        $activeJenjang = $filter['jenjang']; // kosong = semua jenjang
        $kelasOptions  = $activeJenjang
            ? ($kelasByJenjang[$activeJenjang] ?? [])
            : array_merge(...array_values($kelasByJenjang));

        return view('laporan.index', compact(
            'pembayaran', 'rekap', 'perKelas', 'filter', 'filterAktif',
            'jenjangOptions', 'kelasOptions', 'tahunPelajaranList', 'tahunAktif'
        ));
    }

    public function exportPdf(Request $request)
    {
        $user   = auth()->user();
        $filter = $this->sanitizeFilter($request->all(), $user->jenjang);

        $pembayaran = $this->buildQuery($filter)->get();
        $rekap      = $this->hitungRekap($pembayaran);
        $tahunLabel = $this->getTahunLabel($filter['tahun_pelajaran_id']);

        // ── perKelas ──────────────────────────────────────────────────────────
        $tahunId  = $filter['tahun_pelajaran_id'];
        $perKelas = $pembayaran
            ->groupBy(fn($p) =>
                $p->siswa
                    ?->siswaKelas
                    ->firstWhere('tahun_pelajaran_id', $tahunId)
                    ?->kelas
                    ?->nama ?? '-'
            )
            ->map(fn($group) => [
                'jumlah_siswa' => $group->pluck('siswa_id')->unique()->count(),
                'total'        => $group->sum('total_bayar'),
            ])
            ->sortKeys();

        // ── Setting: data instansi dari DB ────────────────────────────────────
        $settingData = $this->buildSettingData($filter['jenjang']);

        $pdf = Pdf::loadView(
                'laporan.cetak',
                compact('pembayaran', 'rekap', 'filter', 'tahunLabel', 'perKelas', 'settingData')
            )
            ->setPaper([0, 0, 609.45, 935.43], 'portrait'); // F4 = 215mm × 330mm

        return $pdf->download('laporan-' . date('Y-m-d') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $user   = auth()->user();
        $filter = $this->sanitizeFilter($request->all(), $user->jenjang);

        return Excel::download(
            new LaporanExport($filter),
            'laporan-' . date('Y-m-d') . '.xlsx'
        );
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function sanitizeFilter(array $raw, ?string $userJenjang): array
    {
        $defaultTahunId = TahunPelajaran::aktif()?->id ?? '';

        return [
            'tahun_pelajaran_id' => $raw['tahun_pelajaran_id'] ?? $defaultTahunId,
            'jenjang'            => $userJenjang ?? ($raw['jenjang'] ?? ''),
            'bulan'              => $raw['bulan']          ?? '',
            'kelas'              => $raw['kelas']          ?? '',
            'tanggal_dari'       => $raw['tanggal_dari']   ?? '',
            'tanggal_sampai'     => $raw['tanggal_sampai'] ?? '',
        ];
    }

    private function buildQuery(array $filter)
    {
        $tahunId = $filter['tahun_pelajaran_id'];

        $query = Pembayaran::with([
                'user',
                'pembayaranBulan',
                // FIX: eager-load siswaKelas + kelas hanya untuk tahun pelajaran
                // yang sedang difilter supaya data kelas yang ditampilkan
                // selalu sesuai dengan tahun yang dipilih
                'siswa' => fn($q) => $q->with([
                    'siswaKelas' => fn($sq) => $sq
                        ->with('kelas')
                        ->when($tahunId, fn($sq2) =>
                            $sq2->where('tahun_pelajaran_id', $tahunId)
                        ),
                ]),
            ])

            // ── Filter Tahun Pelajaran (wajib, default = tahun aktif) ─────────
            ->when(!empty($tahunId), fn($q) =>
                $q->where('tahun_pelajaran_id', $tahunId)
            )

            // ── Filter Jenjang ───────────────────────────────────────────────
            // Kolom 'jenjang' masih ada di tabel siswa → aman
            ->when(!empty($filter['jenjang']), fn($q) =>
                $q->whereHas('siswa', fn($sq) =>
                    $sq->where('jenjang', $filter['jenjang'])
                )
            )

            // ── Filter Kelas ─────────────────────────────────────────────────
            // FIX: kolom 'kelas' sudah TIDAK ada di tabel siswa.
            // Kelas disimpan di tabel siswa_kelas (relasi) → kelas.nama
            ->when(!empty($filter['kelas']), fn($q) =>
                $q->whereHas('siswa.siswaKelas', fn($sq) =>
                    $sq->whereHas('kelas', fn($kq) =>
                        $kq->where('nama', $filter['kelas'] )
                    )
                    ->when(!empty($tahunId), fn($sq2) =>
                        $sq2->where('tahun_pelajaran_id', $tahunId)
                    )
                )
            )

            // ── Filter Tanggal ───────────────────────────────────────────────
            ->when(!empty($filter['tanggal_dari']), fn($q) =>
                $q->where('tanggal_bayar', '>=', $filter['tanggal_dari'])
            )
            ->when(!empty($filter['tanggal_sampai']), fn($q) =>
                $q->where('tanggal_bayar', '<=', $filter['tanggal_sampai'])
            );

        // ── Filter Bulan: berdasarkan tanggal_bayar (bukan bulan yang dibayar) ─
        // Format bulan = 'Y-m' (dari <input type="month">)
        if (!empty($filter['bulan']) && empty($filter['tanggal_dari']) && empty($filter['tanggal_sampai'])) {
            [$tahunBulan, $blnBulan] = explode('-', $filter['bulan']);
            $query->whereYear('tanggal_bayar', $tahunBulan)
                  ->whereMonth('tanggal_bayar', $blnBulan);
        }

        return $query->orderBy('tanggal_bayar');
    }

    private function hitungRekap($pembayaran): array
    {
        return [
            'total_nominal'  => $pembayaran->sum(fn($p) => $p->nominal_per_bulan * $p->jumlah_bulan),
            'total_donator'  => $pembayaran->sum('nominal_donator'),
            'total_mamin'    => $pembayaran->sum('nominal_mamin'),
            'total_semua'    => $pembayaran->sum('total_bayar'),
            'jumlah_record'  => $pembayaran->count(),
        ];
    }

    private function getTahunLabel(?string $tahunPelajaranId): string
    {
        if (empty($tahunPelajaranId)) {
            return 'Semua Tahun';
        }

        $tahun = TahunPelajaran::find($tahunPelajaranId);

        return $tahun ? $tahun->nama : 'Semua Tahun';
    }

    /**
     * Bangun array data setting instansi untuk view cetak.
     * Jika $jenjang kosong → mode yayasan (pakai data global).
     * Jika $jenjang terisi → mode per-jenjang (gabungkan global + jenjang).
     */
    private function buildSettingData(string $jenjang): array
    {
        $all    = Setting::allIndexed();
        $global = $all['global'];

        // Konversi file ke base64 agar DomPDF bisa render gambar
        $toBase64 = function (?string $path): ?string {
            if (!$path || !Storage::disk('public')->exists($path)) return null;
            $mime = Storage::disk('public')->mimeType($path);
            $data = base64_encode(Storage::disk('public')->get($path));
            return "data:{$mime};base64,{$data}";
        };

        $namaYayasan = $global->nama_yayasan  ?: 'Yayasan Pendidikan Kristen';
        $alamat      = collect([$global->alamat, $global->kota])->filter()->join(', ') ?: 'Lasem';
        $telepon     = $global->telepon       ?: '';
        $kota        = $global->kota          ?: 'Lasem';

        if (empty($jenjang)) {
            // ── Mode Yayasan ──────────────────────────────────────────────────
            return [
                'mode'              => 'yayasan',
                'nama_instansi'     => $namaYayasan,
                'nama_yayasan'      => $namaYayasan,
                'alamat'            => $alamat,
                'telepon'           => $telepon,
                'kota'              => $kota,
                'logo_b64'          => $toBase64($global->logo),
                // Nama TTD kiri: pakai nama_admin global jika ada, atau kosong
                'ttd_kiri_nama'     => $global->nama_admin    ?: '',
                'ttd_kiri_jabatan'  => 'Ketua Yayasan',
                'ttd_kiri_b64'      => $toBase64($global->tanda_tangan ?? null),
                // Kanan: bendahara (tidak ada datanya di setting global → kosong)
                'ttd_kanan_nama'     => '',
                'ttd_kanan_jabatan'  => 'Bendahara',
                'ttd_kanan_b64'      => null,
                // Sub-kop (baris kecil di bawah nama instansi)
            ];
        }

        // ── Mode Per Jenjang ──────────────────────────────────────────────────
        $sj = $all[$jenjang] ?? Setting::forJenjang($jenjang);

        $namaSekolah = $sj->nama_sekolah ?: ($jenjang . ' Kristen');

        return [
            'mode'              => 'jenjang',
            'nama_instansi'     => $namaSekolah,
            'nama_yayasan'      => $namaYayasan,
            'alamat'            => $alamat,
            'telepon'           => $telepon,
            'kota'              => $kota,
            'logo_b64'          => $toBase64($sj->logo) ?? $toBase64($global->logo),
            // TTD kiri: kepala sekolah
            'ttd_kiri_nama'     => $sj->nama_kepala_sekolah ?: '',
            'ttd_kiri_nip'      => $sj->nip_kepala_sekolah  ?: '',
            'ttd_kiri_jabatan'  => 'Kepala Sekolah',
            'ttd_kiri_b64'      => $toBase64($sj->tanda_tangan),
            // TTD kanan: bendahara / TU (tidak ada di setting → kosong)
            'ttd_kanan_nama'     => $sj->nama_admin ?: '',
            'ttd_kanan_jabatan'  => 'Tata Usaha',
            'ttd_kanan_b64'      => null,
            // Sub-kop
            'sub_instansi'      => 'Di bawah naungan ' . $namaYayasan,
        ];
    }
}