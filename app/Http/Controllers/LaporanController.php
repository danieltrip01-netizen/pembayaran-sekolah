<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Exports\LaporanExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $user        = auth()->user();
        $userJenjang = $user->jenjang; // null = admin yayasan

        $filter = [
            // ✅ FIX 1: Admin yayasan → jenjang bisa kosong (tampilkan semua).
            //   Admin jenjang → paksa jenjangnya sendiri, tidak bisa diubah.
            'jenjang'        => $userJenjang ?? $request->get('jenjang', ''),

            // ✅ FIX 2: Default bulan KOSONG supaya tampilkan SEMUA data,
            //   bukan hanya bulan ini yang menyebabkan data tidak lengkap.
            'bulan'          => $request->get('bulan', ''),

            'kelas'          => $request->get('kelas', ''),
            'tanggal_dari'   => $request->get('tanggal_dari', ''),
            'tanggal_sampai' => $request->get('tanggal_sampai', ''),
        ];

        $query = Pembayaran::with(['siswa', 'user'])
            // Jenjang: filter hanya jika ada nilainya
            ->when(!empty($filter['jenjang']), fn($q) =>
                $q->whereHas('siswa', fn($sq) => $sq->where('jenjang', $filter['jenjang']))
            )
            // Kelas
            ->when(!empty($filter['kelas']), fn($q) =>
                $q->whereHas('siswa', fn($sq) => $sq->where('kelas', $filter['kelas']))
            )
            // Tanggal
            ->when(!empty($filter['tanggal_dari']), fn($q) =>
                $q->where('tanggal_bayar', '>=', $filter['tanggal_dari'])
            )
            ->when(!empty($filter['tanggal_sampai']), fn($q) =>
                $q->where('tanggal_bayar', '<=', $filter['tanggal_sampai'])
            );

        // Bulan: hanya aktif jika diisi DAN tanggal tidak diisi
        if (!empty($filter['bulan']) && empty($filter['tanggal_dari']) && empty($filter['tanggal_sampai'])) {
            $query->whereJsonContains('bulan_bayar', $filter['bulan']);
        }

        $pembayaran = $query->orderBy('tanggal_bayar')->get();

        $rekap = [
            'total_nominal'  => $pembayaran->sum(fn($p) => $p->nominal_per_bulan * $p->jumlah_bulan),
            'total_donator'  => $pembayaran->sum('nominal_donator'),
            'total_mamin'    => $pembayaran->sum('nominal_mamin'),
            'total_semua'    => $pembayaran->sum('total_bayar'),
            'jumlah_record'  => $pembayaran->count(),
        ];

        // ✅ FIX 3: groupBy pakai closure supaya tidak gagal di dot notation
        $perKelas = $pembayaran
            ->groupBy(fn($p) => $p->siswa->kelas ?? '-')
            ->map(fn($group) => [
                'jumlah_siswa' => $group->pluck('siswa_id')->unique()->count(),
                'total'        => $group->sum('total_bayar'),
            ])
            ->sortKeys();

        // Admin jenjang hanya lihat jenjangnya sendiri; admin yayasan bisa filter semua
        $jenjangOptions = $userJenjang ? [$userJenjang] : ['TK', 'SD', 'SMP'];

        return view('laporan.index', compact(
            'pembayaran', 'rekap', 'perKelas', 'filter', 'jenjangOptions'
        ));
    }

    public function exportPdf(Request $request)
    {
        $user    = auth()->user();
        $filter  = $this->sanitizeFilter($request->all(), $user->jenjang);

        $pembayaran = $this->buildQuery($filter)->get();
        $rekap      = $this->hitungRekap($pembayaran);

        $pdf = Pdf::loadView('laporan.cetak', compact('pembayaran', 'rekap', 'filter'))
                  ->setPaper('a4', 'landscape');

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

    // ─── Helpers ─────────────────────────────────────────────────────

    private function sanitizeFilter(array $raw, ?string $userJenjang): array
    {
        return [
            'jenjang'        => $userJenjang ?? ($raw['jenjang'] ?? ''),
            'bulan'          => $raw['bulan']          ?? '',
            'kelas'          => $raw['kelas']          ?? '',
            'tanggal_dari'   => $raw['tanggal_dari']   ?? '',
            'tanggal_sampai' => $raw['tanggal_sampai'] ?? '',
        ];
    }

    private function buildQuery(array $filter)
    {
        $query = Pembayaran::with(['siswa', 'user'])
            ->when(!empty($filter['jenjang']), fn($q) =>
                $q->whereHas('siswa', fn($sq) => $sq->where('jenjang', $filter['jenjang']))
            )
            ->when(!empty($filter['kelas']), fn($q) =>
                $q->whereHas('siswa', fn($sq) => $sq->where('kelas', $filter['kelas']))
            )
            ->when(!empty($filter['tanggal_dari']), fn($q) =>
                $q->where('tanggal_bayar', '>=', $filter['tanggal_dari'])
            )
            ->when(!empty($filter['tanggal_sampai']), fn($q) =>
                $q->where('tanggal_bayar', '<=', $filter['tanggal_sampai'])
            );

        if (!empty($filter['bulan']) && empty($filter['tanggal_dari']) && empty($filter['tanggal_sampai'])) {
            $query->whereJsonContains('bulan_bayar', $filter['bulan']);
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
}