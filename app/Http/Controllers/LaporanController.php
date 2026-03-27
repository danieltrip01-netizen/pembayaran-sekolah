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

        $defaultTahunId   = $tahunAktif?->id ?? '';
        $tahunPelajaranId = $request->get('tahun_pelajaran_id', $defaultTahunId);

        $filter = [
            'tahun_pelajaran_id' => $tahunPelajaranId,
            'jenjang'            => $userJenjang ?? $request->get('jenjang', ''),
            'bulan'              => $request->get('bulan', now()->format('Y-m')),
            'kelas'              => $request->get('kelas', ''),
            'tanggal_dari'       => $request->get('tanggal_dari', ''),
            'tanggal_sampai'     => $request->get('tanggal_sampai', ''),
        ];

        $pembayaran = $this->buildQuery($filter)->get();
        $rekap      = $this->hitungRekap($pembayaran);

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

        // ── filterAktif ───────────────────────────────────────────────────────
        $filterAktif = [];
        if (!empty($filter['jenjang']))        $filterAktif[] = 'Jenjang: '        . $filter['jenjang'];
        if (!empty($filter['kelas']))          $filterAktif[] = 'Kelas: '          . $filter['kelas'];
        if (!empty($filter['bulan']))          $filterAktif[] = 'Tgl Transaksi: '  . \Carbon\Carbon::createFromFormat('Y-m', $filter['bulan'])->isoFormat('MMMM Y');
        if (!empty($filter['tanggal_dari']))   $filterAktif[] = 'Dari: '           . $filter['tanggal_dari'];
        if (!empty($filter['tanggal_sampai'])) $filterAktif[] = 'Sampai: '         . $filter['tanggal_sampai'];

        $jenjangOptions = $userJenjang ? [$userJenjang] : ['TK', 'SD', 'SMP'];

        $kelasByJenjang = [
            'TK'  => ['A', 'B'],
            'SD'  => ['I', 'II', 'III', 'IV', 'V', 'VI'],
            'SMP' => ['VII', 'VIII', 'IX'],
        ];

        $activeJenjang = $filter['jenjang'];
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
                'siswa' => fn($q) => $q->with([
                    'siswaKelas' => fn($sq) => $sq
                        ->with('kelas')
                        ->when($tahunId, fn($sq2) =>
                            $sq2->where('tahun_pelajaran_id', $tahunId)
                        ),
                ]),
            ])
            ->when(!empty($tahunId), fn($q) =>
                $q->where('tahun_pelajaran_id', $tahunId)
            )
            ->when(!empty($filter['jenjang']), fn($q) =>
                $q->whereHas('siswa', fn($sq) =>
                    $sq->where('jenjang', $filter['jenjang'])
                )
            )
            ->when(!empty($filter['kelas']), fn($q) =>
                $q->whereHas('siswa.siswaKelas', fn($sq) =>
                    $sq->whereHas('kelas', fn($kq) =>
                        $kq->where('nama', $filter['kelas'])
                    )
                    ->when(!empty($tahunId), fn($sq2) =>
                        $sq2->where('tahun_pelajaran_id', $tahunId)
                    )
                )
            )
            ->when(!empty($filter['tanggal_dari']), fn($q) =>
                $q->where('tanggal_bayar', '>=', $filter['tanggal_dari'])
            )
            ->when(!empty($filter['tanggal_sampai']), fn($q) =>
                $q->where('tanggal_bayar', '<=', $filter['tanggal_sampai'])
            );

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
            'total_nominal' => $pembayaran->sum(fn($p) => $p->nominal_per_bulan * $p->jumlah_bulan),
            'total_donator' => $pembayaran->sum('nominal_donator'),
            'total_mamin'   => $pembayaran->sum('nominal_mamin'),
            'total_semua'   => $pembayaran->sum('total_bayar'),
            'jumlah_record' => $pembayaran->count(),
        ];
    }

    private function getTahunLabel(?string $tahunPelajaranId): string
    {
        if (empty($tahunPelajaranId)) return 'Semua Tahun';
        $tahun = TahunPelajaran::find($tahunPelajaranId);
        return $tahun ? $tahun->nama : 'Semua Tahun';
    }

    /**
     * Bangun array $settingData untuk view cetak.
     *
     * Setiap sekolah menyimpan datanya sendiri (tidak ada global setting).
     *
     * • $jenjang kosong  → mode yayasan: ambil nama_yayasan + alamat dari SD
     *                       sebagai referensi (atau fallback jika belum diisi),
     *                       tanpa logo.
     * • $jenjang terisi  → mode per-jenjang: semua data dari setting jenjang tsb.
     */
    private function buildSettingData(string $jenjang): array
    {
        $all = Setting::allIndexed(); // ['TK' => ..., 'SD' => ..., 'SMP' => ...]

        // Helper: konversi path storage → base64 data URI untuk DomPDF
        $toBase64 = function (?string $path): ?string {
            if (!$path || !Storage::disk('public')->exists($path)) return null;
            $mime = Storage::disk('public')->mimeType($path);
            $data = base64_encode(Storage::disk('public')->get($path));
            return "data:{$mime};base64,{$data}";
        };

        if (empty($jenjang)) {
            // ── Mode Yayasan ──────────────────────────────────────────────────
            // Tidak ada global setting; gunakan SD sebagai referensi data yayasan,
            // dengan fallback ke TK atau SMP jika SD belum diisi.
            $ref = $all['SD'] ?? $all['TK'] ?? $all['SMP'] ?? null;

            $namaYayasan = $ref?->nama_yayasan ?: 'Yayasan Pendidikan Kristen';
            $alamat      = collect([$ref?->alamat, $ref?->kota])->filter()->join(', ') ?: 'Lasem';
            $telepon     = $ref?->telepon ?: '';
            $kota        = $ref?->kota    ?: 'Lasem';

            return [
                'mode'             => 'yayasan',
                'nama_instansi'    => $namaYayasan,
                'nama_yayasan'     => $namaYayasan,
                'alamat'           => $alamat,
                'telepon'          => $telepon,
                'kota'             => $kota,
                'logo_b64'         => null, // logo per-sekolah, tidak ada logo yayasan
                'ttd_kiri_jabatan' => 'Ketua Yayasan',
                'ttd_kiri_nama'    => '',
                'ttd_kiri_nip'     => '',
                'ttd_kiri_b64'     => null,
                'ttd_kanan_jabatan'=> 'Bendahara',
                'ttd_kanan_nama'   => '',
                'ttd_kanan_b64'    => null,
            ];
        }

        // ── Mode Per Jenjang ──────────────────────────────────────────────────
        $s = $all[$jenjang] ?? Setting::forJenjang($jenjang);

        $namaYayasan = $s->nama_yayasan ?: 'Yayasan Pendidikan Kristen';
        $namaSekolah = $s->nama_sekolah ?: ($jenjang . ' Kristen Dorkas');
        $alamat      = collect([$s->alamat, $s->kota])->filter()->join(', ') ?: 'Lasem';
        $telepon     = $s->telepon ?: '';
        $kota        = $s->kota    ?: 'Lasem';

        return [
            'mode'             => 'jenjang',
            'nama_instansi'    => $namaSekolah,
            'nama_yayasan'     => $namaYayasan,
            'sub_instansi'     => 'Di bawah naungan ' . $namaYayasan,
            'alamat'           => $alamat,
            'telepon'          => $telepon,
            'kota'             => $kota,
            'logo_b64'         => $toBase64($s->logo),
            'ttd_kiri_jabatan' => 'Kepala Sekolah',
            'ttd_kiri_nama'    => $s->nama_kepala_sekolah ?: '',
            'ttd_kiri_nip'     => $s->nip_kepala_sekolah  ?: '',
            'ttd_kiri_b64'     => $toBase64($s->tanda_tangan),
            'ttd_kanan_jabatan'=> 'Tata Usaha',
            'ttd_kanan_nama'   => $s->nama_admin ?: '',
            'ttd_kanan_b64'    => null,
        ];
    }
}