<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\PembayaranBulan;
use App\Models\Setoran;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user    = Auth::user();
        $jenjang = $user->jenjang; // null = admin yayasan

        // Tahun pelajaran aktif — dipakai di seluruh method ini
        $tahunPelajaran   = TahunPelajaran::aktif();
        $tahunPelajaranId = $tahunPelajaran?->id;

        $bulanIni = Carbon::now()->format('Y-m');

        // ── Siswa ─────────────────────────────────────────────────────────────
        // Hanya hitung siswa yang terdaftar di tahun pelajaran aktif
        $siswaQuery = Siswa::aktif()
            ->when($tahunPelajaranId,
                fn($q) => $q->whereHas('kelasAktif'),   // kelasAktif sudah filter is_active
                fn($q) => $q->whereRaw('0 = 1')          // tidak ada tahun aktif → 0 siswa
            )
            ->when($jenjang, fn($q) => $q->jenjang($jenjang));

        $totalSiswa = $siswaQuery->count();

        $siswaPerJenjang = [];
        if (!$jenjang) {
            // 1 query grouped, bukan 3 query terpisah
            $counts = Siswa::aktif()
                ->when($tahunPelajaranId, fn($q) => $q->whereHas('kelasAktif'))
                ->selectRaw('jenjang, COUNT(*) as total')
                ->groupBy('jenjang')
                ->pluck('total', 'jenjang');

            foreach (['TK', 'SD', 'SMP'] as $j) {
                $siswaPerJenjang[$j] = $counts[$j] ?? 0;
            }
        } else {
            $siswaPerJenjang[$jenjang] = $totalSiswa;
        }

        // ── Pembayaran (dibatasi tahun pelajaran aktif) ───────────────────────
        $pembayaranQuery = Pembayaran::query()
            ->when($tahunPelajaranId,
                fn($q) => $q->where('tahun_pelajaran_id', $tahunPelajaranId),
                fn($q) => $q->whereRaw('0 = 1')
            )
            ->when($jenjang,
                fn($q) => $q->whereHas('siswa', fn($sq) => $sq->where('jenjang', $jenjang))
            );

        $totalPemasukan    = (clone $pembayaranQuery)->sum('total_bayar');
        $pemasukanBulanIni = (clone $pembayaranQuery)
            ->whereDate('tanggal_bayar', '>=', Carbon::parse($bulanIni . '-01')->startOfMonth())
            ->whereDate('tanggal_bayar', '<=', Carbon::parse($bulanIni . '-01')->endOfMonth())
            ->sum('total_bayar');

        $transaksiHariIni = (clone $pembayaranQuery)
            ->whereDate('tanggal_bayar', Carbon::today())
            ->count();

        $pembayaranTerbaru = (clone $pembayaranQuery)
            ->with(['siswa', 'user', 'pembayaranBulan'])
            ->latest('tanggal_bayar')
            ->take(10)
            ->get();

        // ── Setoran ───────────────────────────────────────────────────────────
        $setoranQuery = Setoran::query()
            ->when($jenjang, fn($q) => $q->where('jenjang', $jenjang));

        $totalSetoran = (clone $setoranQuery)->count();

        $setoranTerbaru = (clone $setoranQuery)
            ->with('user')
            ->latest('tanggal_setoran')
            ->take(8)
            ->get();

        $setoranBulanIni = (clone $setoranQuery)
            ->whereDate('tanggal_setoran', '>=', Carbon::parse($bulanIni . '-01')->startOfMonth())
            ->whereDate('tanggal_setoran', '<=', Carbon::parse($bulanIni . '-01')->endOfMonth())
            ->count();

        // ── Grafik ────────────────────────────────────────────────────────────
        $grafikData = $this->getGrafikPemasukan($jenjang, $tahunPelajaranId, $tahunPelajaran);

        // ── Siswa belum bayar bulan ini ───────────────────────────────────────
        // FIX: tidak lagi pakai whereJsonContains('bulan_bayar') yang sudah dihapus.
        // Sekarang cek via tabel pembayaran_bulan.
        $siswaBelumBayar = $this->getSiswaBelumBayar($bulanIni, $jenjang, $tahunPelajaranId);

        // ── Pemasukan per jenjang (admin yayasan) ─────────────────────────────
        $pemasukanPerJenjang = [];
        if (!$jenjang && $tahunPelajaranId) {
            // 1 query grouped, bukan 3 query terpisah
            $totals = Pembayaran::where('tahun_pelajaran_id', $tahunPelajaranId)
                ->join('siswa', 'pembayaran.siswa_id', '=', 'siswa.id')
                ->selectRaw('siswa.jenjang, SUM(pembayaran.total_bayar) as total')
                ->groupBy('siswa.jenjang')
                ->pluck('total', 'jenjang');

            foreach (['TK', 'SD', 'SMP'] as $j) {
                $pemasukanPerJenjang[$j] = (float) ($totals[$j] ?? 0);
            }
        }

        // ── Belum disetor (petugas jenjang) ───────────────────────────────────
        $belumDisetorCount   = 0;
        $belumDisetorNominal = 0;
        if ($jenjang) {
            $belumDisetor = Pembayaran::whereNull('setoran_id')
                ->when($tahunPelajaranId, fn($q) => $q->where('tahun_pelajaran_id', $tahunPelajaranId))
                ->whereHas('siswa', fn($q) => $q->where('jenjang', $jenjang))
                ->get(['total_bayar']);

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
            'tahunPelajaran',
            'jenjang',
        ));
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    /**
     * Data grafik pemasukan Juli – Juni sesuai tahun pelajaran aktif.
     *
     * Tahun pelajaran aktif diambil dari $tahunPelajaran->nama yang
     * berformat "YYYY/YYYY" (contoh: "2024/2025").
     * Jika tidak ada tahun pelajaran aktif, fallback ke tahun ajaran
     * berjalan berdasarkan bulan saat ini (≥ Juli → tahun ini, < Juli → tahun lalu).
     */
    private function getGrafikPemasukan(?string $jenjang, ?int $tahunPelajaranId, $tahunPelajaran = null): array
    {
        $namaBulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

        if ($tahunPelajaran && !empty($tahunPelajaran->nama)) {
            $startYear = (int) substr($tahunPelajaran->nama, 0, 4);
        } else {
            $now       = Carbon::now();
            $startYear = $now->month >= 7 ? $now->year : $now->year - 1;
        }

        $startDate = Carbon::create($startYear, 7, 1)->startOfMonth();
        $endDate   = Carbon::create($startYear + 1, 6, 1)->endOfMonth();

        // 1 query grouped by tahun+bulan, bukan 12 query terpisah
        $rows = Pembayaran::query()
            ->selectRaw('YEAR(tanggal_bayar) as thn, MONTH(tanggal_bayar) as bln, SUM(total_bayar) as total')
            ->when($tahunPelajaranId, fn($q) => $q->where('tahun_pelajaran_id', $tahunPelajaranId))
            ->when($jenjang, fn($q) => $q->whereHas('siswa', fn($sq) => $sq->where('jenjang', $jenjang)))
            ->whereBetween('tanggal_bayar', [$startDate, $endDate])
            ->groupByRaw('YEAR(tanggal_bayar), MONTH(tanggal_bayar)')
            ->get()
            ->keyBy(fn($r) => str_pad((string)$r->thn, 4, '0', STR_PAD_LEFT) . '-' . str_pad((string)$r->bln, 2, '0', STR_PAD_LEFT))
            ->map(fn($r) => $r->total);

        $labels = [];
        $data   = [];

        for ($i = 0; $i < 12; $i++) {
            $tanggal  = $startDate->copy()->addMonths($i);
            $key      = $tanggal->format('Y-m');
            $labels[] = $namaBulan[$tanggal->month - 1] . ' ' . substr((string) $tanggal->year, 2);
            $data[]   = (float) ($rows[$key] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Daftar siswa aktif yang belum membayar untuk bulan tertentu.
     *
     * FIX: kolom bulan_bayar (JSON) sudah dihapus dari tabel pembayaran.
     * Sekarang cek via tabel pembayaran_bulan — siswa belum bayar =
     * siswa yang TIDAK punya record di pembayaran_bulan untuk bulan & tahun pelajaran ini.
     *
     * FIX: $s->kelas tidak lagi ada di model Siswa — kelas kini di tabel siswa_kelas.
     * Eager-load kelasAktif.kelas agar blade bisa pakai $s->kelasAktif?->kelas?->nama.
     */
    private function getSiswaBelumBayar(string $bulan, ?string $jenjang, ?int $tahunPelajaranId)
    {
        if (!$tahunPelajaranId) {
            return collect(); // tidak ada tahun aktif → tidak ada data
        }

        return Siswa::aktif()
            ->with('kelasAktif.kelas')
            ->when($jenjang, fn($q) => $q->jenjang($jenjang))
            ->whereHas('kelasAktif')  // hanya siswa terdaftar di tahun aktif
            ->whereDoesntHave('pembayaranBulan', fn($q) =>
                $q->where('bulan', $bulan)
                  ->whereHas('pembayaran', fn($p) =>
                      $p->where('tahun_pelajaran_id', $tahunPelajaranId)
                  )
            )
            ->orderBy('jenjang')
            ->orderBy('nama')
            ->get();
    }
}