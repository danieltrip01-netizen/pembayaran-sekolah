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
        $jenjang = $user->jenjang; // null = admin yayasan, string = petugas jenjang tertentu

        $bulanIni = Carbon::now()->format('Y-m');

        // ── Siswa ────────────────────────────────────────────────────
        $siswaQuery = Siswa::aktif();
        if ($jenjang) {
            $siswaQuery->jenjang($jenjang);
        }
        $totalSiswa = $siswaQuery->count();

        // Breakdown per jenjang (hanya relevan untuk admin yayasan)
        $siswaPerJenjang = [];
        if (!$jenjang) {
            foreach (['TK', 'SD', 'SMP'] as $j) {
                $siswaPerJenjang[$j] = Siswa::aktif()->jenjang($j)->count();
            }
        } else {
            $siswaPerJenjang[$jenjang] = $totalSiswa;
        }

        // ── Pembayaran ───────────────────────────────────────────────
        $pembayaranQuery = Pembayaran::query();
        if ($jenjang) {
            $pembayaranQuery->whereHas('siswa', fn($q) => $q->where('jenjang', $jenjang));
        }

        $totalPemasukan    = (clone $pembayaranQuery)->sum('total_bayar');
        $pemasukanBulanIni = (clone $pembayaranQuery)
            ->where('tanggal_bayar', 'like', $bulanIni . '%')
            ->sum('total_bayar');

        // Transaksi hari ini
        $transaksiHariIni = (clone $pembayaranQuery)
            ->whereDate('tanggal_bayar', Carbon::today())
            ->count();

        // Pembayaran terbaru (10 terakhir)
        $pembayaranTerbaru = (clone $pembayaranQuery)
            ->with('siswa', 'user')
            ->latest('tanggal_bayar')
            ->take(10)
            ->get();

        // ── Setoran ──────────────────────────────────────────────────
        $setoranQuery = Setoran::query();
        if ($jenjang) {
            $setoranQuery->where('jenjang', $jenjang);
        }

        $totalSetoran = (clone $setoranQuery)->count();

        $setoranTerbaru = (clone $setoranQuery)
            ->with('user')
            ->latest('tanggal_setoran')
            ->take(8)
            ->get();

        // Setoran bulan ini (untuk petugas)
        $setoranBulanIni = (clone $setoranQuery)
            ->where('tanggal_setoran', 'like', $bulanIni . '%')
            ->count();

        // ── Grafik ───────────────────────────────────────────────────
        $grafikData = $this->getGrafikPemasukan($jenjang);

        // ── Siswa belum bayar ────────────────────────────────────────
        $siswaBelumBayar = $this->getSiswaBelumBayar($bulanIni, $jenjang);

        // ── Pemasukan per jenjang (hanya admin yayasan) ──────────────
        $pemasukanPerJenjang = [];
        if (!$jenjang) {
            foreach (['TK', 'SD', 'SMP'] as $j) {
                $pemasukanPerJenjang[$j] = Pembayaran::whereHas(
                    'siswa', fn($q) => $q->where('jenjang', $j)
                )->sum('total_bayar');
            }
        }

        // ── Pembayaran belum disetor (untuk petugas) ─────────────────
        $belumDisetorCount = 0;
        $belumDisetorNominal = 0;
        if ($jenjang) {
            $belumDisetor = Pembayaran::whereNull('setoran_id')
                ->whereHas('siswa', fn($q) => $q->where('jenjang', $jenjang))
                ->get();
            $belumDisetorCount   = $belumDisetor->count();
            $belumDisetorNominal = $belumDisetor->sum('total_bayar');
        }

        return view('dashboard.index', compact(
            'totalSiswa',
            'siswaPerJenjang',
            'totalPemasukan',
            'pemasukanBulanIni',
            'pemasukanPerJenjang',
            'transaksiHariIni',
            'pembayaranTerbaru',
            'totalSetoran',
            'setoranTerbaru',
            'setoranBulanIni',
            'grafikData',
            'siswaBelumBayar',
            'belumDisetorCount',
            'belumDisetorNominal',
            'jenjang',
        ));
    }

    private function getGrafikPemasukan(?string $jenjang): array
    {
        $namaBulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $labels = [];
        $data   = [];

        for ($i = 11; $i >= 0; $i--) {
            $tanggal  = Carbon::now()->subMonths($i);
            $labels[] = $namaBulan[$tanggal->month - 1] . ' ' . substr($tanggal->year, 2);

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
            ->orderBy('kelas')
            ->orderBy('nama')
            ->get();
    }
}