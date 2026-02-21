<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Pembayaran;
use App\Models\Setoran;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user    = auth()->user();
        $jenjang = $user->jenjang; // null jika admin yayasan

        // Query siswa aktif
        $siswaQuery = Siswa::aktif();
        if ($jenjang) {
            $siswaQuery->jenjang($jenjang);
        }
        $totalSiswa = $siswaQuery->count();

        // Total pemasukan keseluruhan
        $pemasukanQuery = Pembayaran::query();
        if ($jenjang) {
            $pemasukanQuery->whereHas('siswa', fn($q) => $q->where('jenjang', $jenjang));
        }
        $totalPemasukan = $pemasukanQuery->sum('total_bayar');

        // Total pemasukan bulan ini
        $bulanIni     = Carbon::now()->format('Y-m');
        $pemasukanBulanIni = (clone $pemasukanQuery)
            ->where('tanggal_bayar', 'like', $bulanIni . '%')
            ->sum('total_bayar');

        // Data grafik pemasukan per bulan (12 bulan terakhir)
        $grafikData = $this->getGrafikPemasukan($jenjang);

        // Siswa belum bayar bulan ini
        $siswaBelumBayar = $this->getSiswaBelumBayar($bulanIni, $jenjang);

        // Setoran terbaru
        $setoranTerbaru = Setoran::with('user')
            ->when($jenjang, fn($q) => $q->where('jenjang', $jenjang))
            ->latest('tanggal_setoran')
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'totalSiswa', 'totalPemasukan', 'pemasukanBulanIni',
            'grafikData', 'siswaBelumBayar', 'setoranTerbaru'
        ));
    }

    private function getGrafikPemasukan(?string $jenjang): array
    {
        $namaBulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $labels    = [];
        $data      = [];

        for ($i = 11; $i >= 0; $i--) {
            $tanggal = Carbon::now()->subMonths($i);
            $labels[] = $namaBulan[$tanggal->month - 1] . ' ' . $tanggal->year;

            $query = Pembayaran::where('tanggal_bayar', 'like', $tanggal->format('Y-m') . '%');
            if ($jenjang) {
                $query->whereHas('siswa', fn($q) => $q->where('jenjang', $jenjang));
            }
            $data[] = (float) $query->sum('total_bayar');
        }

        return ['labels' => $labels, 'data' => $data];
    }

    private function getSiswaBelumBayar(string $bulan, ?string $jenjang)
    {
        return Siswa::aktif()
            ->when($jenjang, fn($q) => $q->jenjang($jenjang))
            ->whereDoesntHave('pembayaran', function ($q) use ($bulan) {
                $q->whereJsonContains('bulan_bayar', $bulan);
            })
            ->with('pembayaran')
            ->take(10)
            ->get();
    }
}