<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Pembayaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

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

    // Cetak kartu siswa (format F4, 4 per halaman)
    public function kartu(Request $request)
    {
        $user        = auth()->user();
        $userJenjang = $user->jenjang;

        $tahunAjaran = (int) date('m') >= 7 ? (int) date('Y') : (int) date('Y') - 1;

        // ── Ambil data siswa ────────────────────────────────────────
        $query = Siswa::aktif()
            ->when($userJenjang, fn($q) => $q->jenjang($userJenjang))
            ->when($request->filled('jenjang') && !$userJenjang, fn($q) => $q->jenjang($request->jenjang))
            ->when($request->filled('kelas'),  fn($q) => $q->where('kelas', $request->kelas))
            ->orderBy('jenjang')->orderBy('kelas')->orderBy('nama');

        $siswaList = $query->with('pembayaran')->get();

        // ── Buat data tabel per siswa ────────────────────────────────
        $urutanBulan = ['07', '08', '09', '10', '11', '12', '01', '02', '03', '04', '05', '06'];
        $namaBulan   = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];

        $items = $siswaList->map(function ($siswa) use ($tahunAjaran, $urutanBulan, $namaBulan) {
            $bulanAktif = $siswa->getBulanAktif($tahunAjaran);

            $tabelBulan = collect($urutanBulan)->map(function ($bln) use (
                $siswa,
                $tahunAjaran,
                $bulanAktif,
                $namaBulan
            ) {
                $tahun   = (int)$bln >= 7 ? $tahunAjaran : $tahunAjaran + 1;
                $periode = sprintf('%04d-%02d', $tahun, $bln);
                $aktif   = in_array($periode, $bulanAktif);

                // Cari pembayaran untuk bulan ini
                $bayar = null;
                if ($aktif) {
                    foreach ($siswa->pembayaran as $p) {
                        $raw   = $p->getRawOriginal('bulan_bayar');
                        $bulanList = \App\Models\Siswa::safeDecode($raw);
                        if (in_array($periode, $bulanList)) {
                            $bayar = $p;
                            break;
                        }
                    }
                }

                return [
                    'bulan'        => $namaBulan[$bln],
                    'aktif'        => $aktif,
                    // ✅ Rumus: uang_sekolah = SPP - Donatur (yang ditampilkan di kolom SPP)
                    'uang_sekolah' => (float) $siswa->nominal_pembayaran,
                    'donatur'      => (float) $siswa->nominal_donator,
                    'yang_dibayar' => $bayar ? (float) $bayar->total_bayar / $bayar->jumlah_bulan : null,
                    'tanggal_bayar' => $bayar ? $bayar->tanggal_bayar->format('d/m/y') : null,
                ];
            });

            return [
                'siswa'       => $siswa,
                'tabel_bulan' => $tabelBulan,
            ];
        });

        // ── 4 kartu per halaman ─────────────────────────────────────
        $chunks = $items->chunk(4);

        // ── Config nama sekolah & kepala sekolah ────────────────────
        // Ambil dari config atau env; sesuaikan di config/sekolah.php jika ada
        $kepsekMap = [
            'TK'  => config('sekolah.kepsek_tk',  env('KEPSEK_TK',  'Kepala Sekolah TK')),
            'SD'  => config('sekolah.kepsek_sd',  env('KEPSEK_SD',  'Kepala Sekolah SD')),
            'SMP' => config('sekolah.kepsek_smp', env('KEPSEK_SMP', 'Kepala Sekolah SMP')),
        ];

        $namaYayasanMap = [
            'TK'  => config('sekolah.yayasan', 'Yayasan Pendidikan Kristen'),
            'SD'  => config('sekolah.yayasan', 'Yayasan Pendidikan Kristen'),
            'SMP' => config('sekolah.yayasan', 'Yayasan Pendidikan Kristen'),
        ];

        $namaSekolahMap = [
            'TK'  => config('sekolah.nama_tk',  'TK Kristen'),
            'SD'  => config('sekolah.nama_sd',  'SD Kristen'),
            'SMP' => config('sekolah.nama_smp', 'SMP Kristen'),
        ];

        $alamatMap = [
            'TK'  => config('sekolah.alamat_tk',  config('sekolah.alamat', '')),
            'SD'  => config('sekolah.alamat_sd',  config('sekolah.alamat', '')),
            'SMP' => config('sekolah.alamat_smp', config('sekolah.alamat', '')),
        ];

        $kota = config('sekolah.kota', 'Lasem');

        $pdf = Pdf::loadView('cetak.kartu', compact(
            'chunks',
            'tahunAjaran',
            'kepsekMap',
            'namaYayasanMap',
            'namaSekolahMap',
            'alamatMap',
            'kota'
        ))->setPaper([0, 0, 609.4, 935.4], 'portrait'); // F4

        return $pdf->stream('kartu-spp-' . date('Ymd') . '.pdf');
    }

    /**
     * Kartu per siswa (1 kartu, A5 atau setengah F4).
     */
    public function kartuSiswa(Siswa $siswa)
    {
        $tahunAjaran = (int) date('m') >= 7 ? (int) date('Y') : (int) date('Y') - 1;
        $siswa->load('pembayaran');

        // Re-use logic dari method kartu()
        $urutanBulan = ['07', '08', '09', '10', '11', '12', '01', '02', '03', '04', '05', '06'];
        $namaBulan   = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];

        $bulanAktif = $siswa->getBulanAktif($tahunAjaran);

        $tabelBulan = collect($urutanBulan)->map(function ($bln) use ($siswa, $tahunAjaran, $bulanAktif, $namaBulan) {
            $tahun   = (int)$bln >= 7 ? $tahunAjaran : $tahunAjaran + 1;
            $periode = sprintf('%04d-%02d', $tahun, $bln);
            $aktif   = in_array($periode, $bulanAktif);

            $bayar = null;
            if ($aktif) {
                foreach ($siswa->pembayaran as $p) {
                    $raw  = $p->getRawOriginal('bulan_bayar');
                    $list = \App\Models\Siswa::safeDecode($raw);
                    if (in_array($periode, $list)) {
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

        $kepsekMap = [
            'TK'  => config('sekolah.kepsek_tk',  'Kepala Sekolah TK'),
            'SD'  => config('sekolah.kepsek_sd',  'Kepala Sekolah SD'),
            'SMP' => config('sekolah.kepsek_smp', 'Kepala Sekolah SMP'),
        ];

        $namaYayasanMap = ['TK' => '', 'SD' => '', 'SMP' => ''];
        $namaSekolahMap = ['TK' => '', 'SD' => '', 'SMP' => ''];
        $alamatMap      = ['TK' => '', 'SD' => '', 'SMP' => ''];

        foreach (['TK', 'SD', 'SMP'] as $j) {
            $namaYayasanMap[$j] = config('sekolah.yayasan', 'Yayasan Pendidikan Kristen');
            $namaSekolahMap[$j] = config("sekolah.nama_{$j}", "$j Kristen");
            $alamatMap[$j]      = config("sekolah.alamat_{$j}", config('sekolah.alamat', ''));
        }

        $kota   = config('sekolah.kota', 'Lasem');
        $chunks = collect([collect([['siswa' => $siswa, 'tabel_bulan' => $tabelBulan]])]); // 1 kartu saja

        $pdf = Pdf::loadView('cetak.kartu', compact(
            'chunks',
            'tahunAjaran',
            'kepsekMap',
            'namaYayasanMap',
            'namaSekolahMap',
            'alamatMap',
            'kota'
        ))->setPaper('a5', 'portrait');

        return $pdf->stream('kartu-' . $siswa->id_siswa . '.pdf');
    }
}
