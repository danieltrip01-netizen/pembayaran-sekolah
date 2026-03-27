<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Siswa;
use App\Models\Pembayaran;
use App\Models\TahunPelajaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CetakController extends Controller
{
    // ── Halaman pilih siswa untuk cetak kartu ────────────────────────────────

    public function index(Request $request)
    {
        $user        = auth()->user();
        $jenjang     = $user->jenjang;
        $tahunAktif  = TahunPelajaran::aktif();

        $siswa = Siswa::aktif()
            ->when($jenjang, fn($q) => $q->jenjang($jenjang))
            ->when($tahunAktif, fn($q) =>
                $q->whereHas('siswaKelas', fn($sq) =>
                    $sq->where('tahun_pelajaran_id', $tahunAktif->id)
                )
            )
            ->when($request->kelas, fn($q) =>
                $q->whereHas('siswaKelas.kelas', fn($kq) =>
                    $kq->where('nama', $request->kelas)
                )
            )
            ->with([
                'siswaKelas' => fn($q) => $q
                    ->when($tahunAktif, fn($sq) =>
                        $sq->where('tahun_pelajaran_id', $tahunAktif->id)
                    )
                    ->with('kelas'),
            ])
            ->get()
            ->sortBy(fn($s) =>
                $s->jenjang . '|'
                . str_pad($s->siswaKelas->first()?->kelas?->urutan ?? 99, 3, '0', STR_PAD_LEFT) . '|'
                . $s->nama
            )
            ->values();

        return view('cetak.index', compact('siswa', 'jenjang', 'tahunAktif'));
    }

    // ── Cetak kartu siswa (format F4, 4 per halaman) ─────────────────────────

    public function kartu(Request $request)
    {
        $request->validate([
            'siswa_ids'           => 'required|array|min:1',
            'siswa_ids.*'         => 'exists:siswa,id',
            'tahun_pelajaran_id'  => 'nullable|exists:tahun_pelajaran,id',
        ]);

        $user        = auth()->user();
        $userJenjang = $user->jenjang;

        $tahunPelajaran = $request->filled('tahun_pelajaran_id')
            ? TahunPelajaran::findOrFail($request->tahun_pelajaran_id)
            : TahunPelajaran::aktif();

        $tahunAjaran = $tahunPelajaran
            ? (int) $tahunPelajaran->tanggal_mulai->format('Y')
            : ((int) date('m') >= 7 ? (int) date('Y') : (int) date('Y') - 1);

        $siswaList = Siswa::whereIn('id', $request->siswa_ids)
            ->when($userJenjang, fn($q) => $q->where('jenjang', $userJenjang))
            ->with([
                'pembayaran' => fn($q) => $q
                    ->when($tahunPelajaran, fn($sq) =>
                        $sq->where('tahun_pelajaran_id', $tahunPelajaran->id)
                    ),
                'pembayaran.pembayaranBulan',
                'siswaKelas' => fn($q) => $q
                    ->when($tahunPelajaran, fn($sq) =>
                        $sq->where('tahun_pelajaran_id', $tahunPelajaran->id)
                    )
                    ->with('kelas'),
            ])
            ->get()
            ->sortBy(fn($s) =>
                $s->jenjang . '|'
                . str_pad($s->getKelasForTahun($tahunPelajaran)?->urutan ?? 99, 3, '0', STR_PAD_LEFT) . '|'
                . $s->nama
            )
            ->values();

        $items  = $this->buildItemsTabel($siswaList, $tahunAjaran, $tahunPelajaran);
        $chunks = $items->chunk(4);

        $maps = $this->buildSettingMaps();

        $pdf = Pdf::loadView('cetak.kartu', array_merge(compact('chunks', 'tahunAjaran'), $maps))
            ->setPaper([0, 0, 609.4, 935.4], 'portrait'); // F4

        return $pdf->stream('kartu-spp-' . date('Ymd') . '.pdf');
    }

    // ── Kartu per siswa (1 kartu, A5) ────────────────────────────────────────

    public function kartuSiswa(Siswa $siswa)
    {
        $tahunPelajaran = TahunPelajaran::aktif();
        $tahunAjaran    = $tahunPelajaran
            ? (int) $tahunPelajaran->tanggal_mulai->format('Y')
            : ((int) date('m') >= 7 ? (int) date('Y') : (int) date('Y') - 1);

        $siswa->load([
            'pembayaran' => fn($q) => $q
                ->when($tahunPelajaran, fn($sq) =>
                    $sq->where('tahun_pelajaran_id', $tahunPelajaran->id)
                ),
            'pembayaran.pembayaranBulan',
            'siswaKelas' => fn($q) => $q
                ->when($tahunPelajaran, fn($sq) =>
                    $sq->where('tahun_pelajaran_id', $tahunPelajaran->id)
                )
                ->with('kelas'),
        ]);

        $items  = $this->buildItemsTabel(collect([$siswa]), $tahunAjaran, $tahunPelajaran);
        $chunks = collect([collect([$items->first()])]);

        $maps = $this->buildSettingMaps();

        $pdf = Pdf::loadView('cetak.kartu', array_merge(compact('chunks', 'tahunAjaran'), $maps))
            ->setPaper('a5', 'portrait');

        return $pdf->stream('kartu-' . $siswa->id_siswa . '.pdf');
    }

    // ════════════════════════════════════════════════════════════════════════
    //  PRIVATE HELPERS
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Bangun semua map setting per-jenjang.
     * Setiap sekolah (TK/SD/SMP) menyimpan datanya sendiri — tidak ada global setting.
     */
    private function buildSettingMaps(): array
    {
        $all = Setting::allIndexed(); // ['TK' => ..., 'SD' => ..., 'SMP' => ...]

        $kepsekMap      = [];
        $namaYayasanMap = [];
        $namaSekolahMap = [];
        $alamatMap      = [];
        $kotaMap        = [];
        $logoDataMap    = [];
        $ttdDataMap     = [];

        foreach (['TK', 'SD', 'SMP'] as $j) {
            $s = $all[$j] ?? null;

            $kepsekMap[$j]      = $s?->nama_kepala_sekolah ?: "Kepala Sekolah $j";
            $namaYayasanMap[$j] = $s?->nama_yayasan        ?: 'Yayasan Kristen Dorkas';
            $namaSekolahMap[$j] = $s?->nama_sekolah        ?: "$j Kristen Dorkas";
            $alamatMap[$j]      = collect([$s?->alamat, $s?->kota])->filter()->join(', ');
            $kotaMap[$j]        = $s?->kota                ?: 'Lasem';

            $logoDataMap[$j] = '';
            if ($s?->logo && Storage::disk('public')->exists($s->logo)) {
                $mime            = Storage::disk('public')->mimeType($s->logo);
                $logoDataMap[$j] = "data:{$mime};base64,"
                    . base64_encode(Storage::disk('public')->get($s->logo));
            }

            $ttdDataMap[$j] = '';
            if ($s?->tanda_tangan && Storage::disk('public')->exists($s->tanda_tangan)) {
                $mime           = Storage::disk('public')->mimeType($s->tanda_tangan);
                $ttdDataMap[$j] = "data:{$mime};base64,"
                    . base64_encode(Storage::disk('public')->get($s->tanda_tangan));
            }
        }

        return compact(
            'kepsekMap', 'namaYayasanMap', 'namaSekolahMap',
            'alamatMap', 'kotaMap', 'logoDataMap', 'ttdDataMap'
        ) + [
            'settingMap' => $all,
        ];
    }

    /**
     * Buat collection item [{siswa, kelas_nama, nominal_spp, nominal_donator,
     * nominal_mamin, tabel_bulan, qr_src}] untuk semua siswa.
     */
    private function buildItemsTabel(
        $siswaList,
        int $tahunAjaran,
        ?TahunPelajaran $tahunPelajaran = null
    ): \Illuminate\Support\Collection {
        $urutanBulan = ['07', '08', '09', '10', '11', '12', '01', '02', '03', '04', '05', '06'];
        $namaBulan   = [
            '01' => 'Januari',  '02' => 'Februari', '03' => 'Maret',
            '04' => 'April',    '05' => 'Mei',       '06' => 'Juni',
            '07' => 'Juli',     '08' => 'Agustus',   '09' => 'September',
            '10' => 'Oktober',  '11' => 'November',  '12' => 'Desember',
        ];

        return collect($siswaList)->map(function ($siswa) use (
            $tahunAjaran, $urutanBulan, $namaBulan, $tahunPelajaran
        ) {
            $ka           = $siswa->getKelasForTahun($tahunPelajaran);
            $nominalSpp   = (float) ($ka?->nominal_spp      ?? 0);
            $nominalDon   = (float) ($ka?->nominal_donator  ?? 0);
            $nominalMamin = $siswa->jenjang === 'TK' ? (float) ($ka?->nominal_mamin ?? 0) : 0.0;
            $kelasNama    = $ka?->kelas?->nama ?? '-';

            $bulanAktif = $siswa->getBulanAktif($tahunAjaran);

            $bulanToPembayaran = [];
            foreach ($siswa->pembayaran as $p) {
                $bulanList = $p->relationLoaded('pembayaranBulan')
                    ? $p->pembayaranBulan->pluck('bulan')
                    : $p->pembayaranBulan()->pluck('bulan');

                foreach ($bulanList as $b) {
                    $bulanToPembayaran[$b] = $p;
                }
            }

            $tabelBulan = collect($urutanBulan)->map(function ($bln) use (
                $tahunAjaran, $bulanAktif, $namaBulan,
                $bulanToPembayaran, $nominalSpp, $nominalDon, $nominalMamin
            ) {
                $tahun   = (int) $bln >= 7 ? $tahunAjaran : $tahunAjaran + 1;
                $periode = sprintf('%04d-%02d', $tahun, $bln);
                $aktif   = in_array($periode, $bulanAktif);
                $bayar   = $aktif ? ($bulanToPembayaran[$periode] ?? null) : null;

                return [
                    'bulan'         => $namaBulan[$bln],
                    'periode'       => $periode,
                    'aktif'         => $aktif,
                    'uang_sekolah'  => $bayar
                        ? (float) $bayar->nominal_per_bulan
                        : $nominalSpp,
                    'donatur'       => $bayar
                        ? (float) $bayar->nominal_donator / max(1, $bayar->jumlah_bulan)
                        : $nominalDon,
                    'mamin'         => $bayar
                        ? (float) $bayar->nominal_mamin / max(1, $bayar->jumlah_bulan)
                        : $nominalMamin,
                    'yang_dibayar'  => $bayar
                        ? (float) $bayar->total_bayar / max(1, $bayar->jumlah_bulan)
                        : null,
                    'tanggal_bayar' => $bayar
                        ? $bayar->tanggal_bayar->format('d/m/y')
                        : null,
                ];
            });

            $qrSrc = '';
            try {
                // Gunakan signed URL untuk route siswa.riwayat.publik
                $riwayatUrl = \Illuminate\Support\Facades\URL::signedRoute(
                    'siswa.riwayat.publik',
                    ['siswa' => $siswa->id]
                );
                $qrRaw  = QrCode::format('svg')
                            ->size(112)
                            ->margin(2)
                            ->errorCorrection('M')
                            ->generate($riwayatUrl);
                $qrSrc = 'data:image/svg+xml;base64,' . base64_encode((string) $qrRaw);
            } catch (\Throwable $e) {
                // QR gagal generate — kartu tetap tercetak tanpa QR
            }

            return [
                'siswa'           => $siswa,
                'kelas_nama'      => $kelasNama,
                'nominal_spp'     => $nominalSpp,
                'nominal_donator' => $nominalDon,
                'nominal_mamin'   => $nominalMamin,
                'tabel_bulan'     => $tabelBulan,
                'qr_src'          => $qrSrc,
            ];
        });
    }
}