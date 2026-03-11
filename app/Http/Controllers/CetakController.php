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

            // FIX: filter kelas sekarang lewat relasi siswa_kelas → kelas
            ->when($request->kelas, fn($q) =>
                $q->whereHas('kelasAktif.kelas', fn($kq) =>
                    $kq->where('nama', $request->kelas)
                )
            )

            // Eager-load supaya sortBy & tampilan tidak N+1
            ->with(['kelasAktif.kelas'])
            ->get()

            // FIX: orderBy 'kelas' tidak bisa di SQL karena kolom sudah di tabel lain;
            //      urutkan di PHP setelah data loaded
            ->sortBy(fn($s) =>
                $s->jenjang . '|'
                . str_pad($s->kelasAktif?->kelas?->urutan ?? 99, 3, '0', STR_PAD_LEFT) . '|'
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

        // Utamakan tahun pelajaran dari form, fallback ke tahun aktif
        $tahunPelajaran = $request->filled('tahun_pelajaran_id')
            ? TahunPelajaran::findOrFail($request->tahun_pelajaran_id)
            : TahunPelajaran::aktif();

        $tahunAjaran = $tahunPelajaran
            ? (int) $tahunPelajaran->tanggal_mulai->format('Y')
            : ((int) date('m') >= 7 ? (int) date('Y') : (int) date('Y') - 1);

        // FIX: eager-load relasi yang dibutuhkan buildItemsTabel
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

        // FIX: eager-load relasi lengkap
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

        $items      = $this->buildItemsTabel(collect([$siswa]), $tahunAjaran, $tahunPelajaran);
        $chunks     = collect([collect([$items->first()])]);

        $maps = $this->buildSettingMaps();

        $pdf = Pdf::loadView('cetak.kartu', array_merge(compact('chunks', 'tahunAjaran'), $maps))
            ->setPaper('a5', 'portrait');

        return $pdf->stream('kartu-' . $siswa->id_siswa . '.pdf');
    }

    // ════════════════════════════════════════════════════════════════════════
    //  PRIVATE HELPERS
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Ambil semua setting dari DB dan bangun map yang dibutuhkan view cetak.
     */
    private function buildSettingMaps(): array
    {
        $all    = Setting::allIndexed();
        $global = $all['global'];

        $namaYayasan = $global->nama_yayasan ?: 'Yayasan Kristen';
        $alamat      = collect([$global->alamat, $global->kota])->filter()->join(', ');
        $kota        = $global->kota ?: 'Lasem';

        $kepsekMap = $namaYayasanMap = $namaSekolahMap = $alamatMap = $logoDataMap = $ttdDataMap = [];

        foreach (['TK', 'SD', 'SMP'] as $j) {
            $s = $all[$j];

            $kepsekMap[$j]      = $s->nama_kepala_sekolah ?: "Kepala Sekolah $j";
            $namaYayasanMap[$j] = $namaYayasan;
            $namaSekolahMap[$j] = $s->nama_sekolah ?: "$j Kristen";
            $alamatMap[$j]      = $alamat;

            $logoDataMap[$j] = '';
            if ($s->logo && Storage::disk('public')->exists($s->logo)) {
                $mime = Storage::disk('public')->mimeType($s->logo);
                $logoDataMap[$j] = "data:{$mime};base64,"
                    . base64_encode(Storage::disk('public')->get($s->logo));
            }

            $ttdDataMap[$j] = '';
            if ($s->tanda_tangan && Storage::disk('public')->exists($s->tanda_tangan)) {
                $mime = Storage::disk('public')->mimeType($s->tanda_tangan);
                $ttdDataMap[$j] = "data:{$mime};base64,"
                    . base64_encode(Storage::disk('public')->get($s->tanda_tangan));
            }
        }

        return compact(
            'kepsekMap', 'namaYayasanMap', 'namaSekolahMap',
            'alamatMap', 'kota', 'logoDataMap', 'ttdDataMap'
        ) + [
            'globalSetting' => $global,
            'settingMap'    => $all,
        ];
    }

    /**
     * Buat collection item [{siswa, kelas_nama, nominal_spp, nominal_donator,
     * nominal_mamin, tabel_bulan}] untuk semua siswa.
     *
     * FIX: Tidak lagi mengandalkan kolom kelas/nominal_* di tabel siswa.
     *      Semua diambil dari relasi siswa_kelas.
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
            // FIX: ambil nominal dari siswa_kelas (bukan kolom di tabel siswa)
            $ka           = $siswa->getKelasForTahun($tahunPelajaran);
            $nominalSpp   = (float) ($ka?->nominal_spp      ?? 0);
            $nominalDon   = (float) ($ka?->nominal_donator  ?? 0);
            $nominalMamin = $siswa->jenjang === 'TK' ? (float) ($ka?->nominal_mamin ?? 0) : 0.0;
            $kelasNama    = $ka?->kelas?->nama ?? '-';

            // Daftar bulan aktif siswa
            $bulanAktif = $siswa->getBulanAktif($tahunAjaran);

            // FIX: Indeks pembayaran per bulan menggunakan relasi pembayaranBulan,
            //      bukan JSON bulan_bayar + Siswa::safeDecode() yang sudah dihapus
            $bulanToPembayaran = [];
            foreach ($siswa->pembayaran as $p) {
                // Pastikan pembayaranBulan sudah di-eager-load
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
                    // Nominal SPP dari siswa_kelas (snapshot saat transaksi jika ada)
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

            // Generate QR di controller — DomPDF andal render <img> data URI
            $qrSrc = '';
            try {
                $riwayatUrl = route('siswa.riwayat', $siswa->id);
                $qrRaw      = QrCode::format('svg')
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