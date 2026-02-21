<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Siswa;
use App\Models\Pembayaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CetakController extends Controller
{
    // Halaman pilih siswa untuk cetak kartu
    public function index(Request $request)
    {
        $user    = auth()->user();
        $jenjang = $user->jenjang;

        $siswa = Siswa::aktif()
            ->when($jenjang, fn($q) => $q->jenjang($jenjang))
            ->when($request->kelas, fn($q) => $q->where('kelas', $request->kelas))
            ->orderBy('kelas')->orderBy('nama')
            ->get();

        return view('cetak.index', compact('siswa', 'jenjang'));
    }

    // ── Cetak kartu siswa (format F4, 4 per halaman) ─────────────────
    public function kartu(Request $request)
    {
        $request->validate([
            'siswa_ids'    => 'required|array|min:1',
            'siswa_ids.*'  => 'exists:siswa,id',
            'tahun_ajaran' => 'nullable|integer|min:2000|max:2100',
        ]);

        $user        = auth()->user();
        $userJenjang = $user->jenjang;

        // Gunakan tahun ajaran dari form, fallback ke tahun berjalan
        $tahunAjaran = $request->filled('tahun_ajaran')
            ? (int) $request->tahun_ajaran
            : ((int) date('m') >= 7 ? (int) date('Y') : (int) date('Y') - 1);

        // ── Ambil HANYA siswa yang dipilih dari form ─────────────────
        // Filter jenjang sebagai pengaman: admin jenjang tidak bisa cetak siswa jenjang lain
        $siswaList = Siswa::whereIn('id', $request->siswa_ids)
            ->when($userJenjang, fn($q) => $q->where('jenjang', $userJenjang))
            ->with('pembayaran')
            ->orderBy('jenjang')->orderBy('kelas')->orderBy('nama')
            ->get();

        // ── Buat data tabel per siswa ────────────────────────────────
        $items = $this->buildItemsTabel($siswaList, $tahunAjaran);

        // ── 4 kartu per halaman ──────────────────────────────────────
        $chunks = $items->chunk(4);

        // ── Ambil setting dari DB ────────────────────────────────────
        [
            'kepsekMap'      => $kepsekMap,
            'namaYayasanMap' => $namaYayasanMap,
            'namaSekolahMap' => $namaSekolahMap,
            'alamatMap'      => $alamatMap,
            'kota'           => $kota,
            'logoDataMap'    => $logoDataMap,
            'ttdDataMap'     => $ttdDataMap,
        ] = $this->buildSettingMaps();

        $pdf = Pdf::loadView('cetak.kartu', compact(
            'chunks',
            'tahunAjaran',
            'kepsekMap',
            'namaYayasanMap',
            'namaSekolahMap',
            'alamatMap',
            'kota',
            'logoDataMap',
            'ttdDataMap'
        ))->setPaper([0, 0, 609.4, 935.4], 'portrait'); // F4

        return $pdf->stream('kartu-spp-' . date('Ymd') . '.pdf');
    }

    // ── Kartu per siswa (1 kartu, A5) ────────────────────────────────
    public function kartuSiswa(Siswa $siswa)
    {
        $tahunAjaran = (int) date('m') >= 7 ? (int) date('Y') : (int) date('Y') - 1;
        $siswa->load('pembayaran');

        $items      = $this->buildItemsTabel(collect([$siswa]), $tahunAjaran);
        $tabelBulan = $items->first()['tabel_bulan'];
        $chunks     = collect([collect([['siswa' => $siswa, 'tabel_bulan' => $tabelBulan]])]);

        [
            'kepsekMap'      => $kepsekMap,
            'namaYayasanMap' => $namaYayasanMap,
            'namaSekolahMap' => $namaSekolahMap,
            'alamatMap'      => $alamatMap,
            'kota'           => $kota,
            'logoDataMap'    => $logoDataMap,
            'ttdDataMap'     => $ttdDataMap,
        ] = $this->buildSettingMaps();

        $pdf = Pdf::loadView('cetak.kartu', compact(
            'chunks',
            'tahunAjaran',
            'kepsekMap',
            'namaYayasanMap',
            'namaSekolahMap',
            'alamatMap',
            'kota',
            'logoDataMap',
            'ttdDataMap'
        ))->setPaper('a5', 'portrait');

        return $pdf->stream('kartu-' . $siswa->id_siswa . '.pdf');
    }

    // ════════════════════════════════════════════════════════════════
    //  PRIVATE HELPERS
    // ════════════════════════════════════════════════════════════════

    /**
     * Ambil semua setting dari DB dan bangun map yang dibutuhkan view cetak.
     */
    public function buildSettingMaps(): array
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
                $logoDataMap[$j] = "data:{$mime};base64," . base64_encode(Storage::disk('public')->get($s->logo));
            }

            $ttdDataMap[$j] = '';
            if ($s->tanda_tangan && Storage::disk('public')->exists($s->tanda_tangan)) {
                $mime = Storage::disk('public')->mimeType($s->tanda_tangan);
                $ttdDataMap[$j] = "data:{$mime};base64," . base64_encode(Storage::disk('public')->get($s->tanda_tangan));
            }
        }

        return compact(
            'kepsekMap', 'namaYayasanMap', 'namaSekolahMap',
            'alamatMap', 'kota', 'logoDataMap', 'ttdDataMap', 'global'
        ) + ['globalSetting' => $global, 'settingMap' => $all];
    }

    /**
     * Buat collection item [{siswa, tabel_bulan}] untuk semua siswa.
     */
    private function buildItemsTabel($siswaList, int $tahunAjaran): \Illuminate\Support\Collection
    {
        $urutanBulan = ['07', '08', '09', '10', '11', '12', '01', '02', '03', '04', '05', '06'];
        $namaBulan   = [
            '01' => 'Januari',  '02' => 'Februari', '03' => 'Maret',
            '04' => 'April',    '05' => 'Mei',       '06' => 'Juni',
            '07' => 'Juli',     '08' => 'Agustus',   '09' => 'September',
            '10' => 'Oktober',  '11' => 'November',  '12' => 'Desember',
        ];

        return collect($siswaList)->map(function ($siswa) use ($tahunAjaran, $urutanBulan, $namaBulan) {
            $bulanAktif = $siswa->getBulanAktif($tahunAjaran);

            $tabelBulan = collect($urutanBulan)->map(function ($bln) use (
                $siswa, $tahunAjaran, $bulanAktif, $namaBulan
            ) {
                $tahun   = (int) $bln >= 7 ? $tahunAjaran : $tahunAjaran + 1;
                $periode = sprintf('%04d-%02d', $tahun, $bln);
                $aktif   = in_array($periode, $bulanAktif);

                $bayar = null;
                if ($aktif) {
                    foreach ($siswa->pembayaran as $p) {
                        $raw       = $p->getRawOriginal('bulan_bayar');
                        $bulanList = \App\Models\Siswa::safeDecode($raw);
                        if (in_array($periode, $bulanList)) {
                            $bayar = $p;
                            break;
                        }
                    }
                }

                return [
                    'bulan'         => $namaBulan[$bln],
                    'aktif'         => $aktif,
                    'uang_sekolah'  => (float) $siswa->nominal_pembayaran,
                    'donatur'       => (float) $siswa->nominal_donator,
                    'yang_dibayar'  => $bayar ? (float) $bayar->total_bayar / $bayar->jumlah_bulan : null,
                    'tanggal_bayar' => $bayar ? $bayar->tanggal_bayar->format('d/m/y') : null,
                ];
            });

            return ['siswa' => $siswa, 'tabel_bulan' => $tabelBulan];
        });
    }
}