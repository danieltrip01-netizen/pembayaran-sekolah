<?php

namespace App\Exports;

use App\Models\Pembayaran;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LaporanExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    private int $rowNumber = 0;

    public function __construct(private readonly array $filter) {}

    public function title(): string
    {
        return 'Laporan Pembayaran';
    }

    // FIX: Gunakan FromQuery (lebih efisien untuk data besar) dan perbaiki semua filter
    public function query()
    {
        $tahunId = $this->filter['tahun_pelajaran_id'] ?? null;

        return Pembayaran::with([
            'siswa',
            'user',
            'pembayaranBulan',
            'siswa.siswaKelas' => fn($sq) => $sq
                ->with('kelas')
                ->when(
                    $tahunId,
                    fn($sq2) =>
                    $sq2->where('tahun_pelajaran_id', $tahunId)
                ),
        ])

            ->when(
                !empty($tahunId),
                fn($q) =>
                $q->where('tahun_pelajaran_id', $tahunId)
            )

            ->when(
                !empty($this->filter['jenjang']),
                fn($q) =>
                $q->whereHas(
                    'siswa',
                    fn($sq) =>
                    $sq->where('jenjang', $this->filter['jenjang'])
                )
            )

            ->when(
                !empty($this->filter['kelas']),
                fn($q) =>
                $q->whereHas(
                    'siswa.siswaKelas',
                    fn($sq) =>
                    $sq->whereHas(
                        'kelas',
                        fn($kq) =>
                        $kq->where('nama', $this->filter['kelas'])
                    )
                        ->when(
                            !empty($tahunId),
                            fn($sq2) =>
                            $sq2->where('tahun_pelajaran_id', $tahunId)
                        )
                )
            )

            // FIX: konsisten dengan buildQuery() di controller
            ->when(
                !empty($this->filter['bulan'])
                    && empty($this->filter['tanggal_dari'])
                    && empty($this->filter['tanggal_sampai']),
                function ($q) {
                    [$tahunBulan, $blnBulan] = explode('-', $this->filter['bulan']);
                    return $q->whereYear('tanggal_bayar', $tahunBulan)
                        ->whereMonth('tanggal_bayar', $blnBulan);
                }
            )

            ->when(
                !empty($this->filter['tanggal_dari']),
                fn($q) =>
                $q->where('tanggal_bayar', '>=', $this->filter['tanggal_dari'])
            )
            ->when(
                !empty($this->filter['tanggal_sampai']),
                fn($q) =>
                $q->where('tanggal_bayar', '<=', $this->filter['tanggal_sampai'])
            )

            ->orderBy('tanggal_bayar')
            ->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Bayar',
            'Tanggal',
            'ID Siswa',
            'Nama Siswa',
            'Kelas',          // FIX: diambil dari siswaKelas → kelas
            'Jenjang',
            'Bulan Dibayar',
            'Jumlah Bulan',
            'Nominal/Bulan',
            'Donatur',
            'Mamin',
            'Kredit Digunakan',
            'Total Bayar',
            'Petugas',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;

        $tahunId = $this->filter['tahun_pelajaran_id'] ?? null;

        // Ambil kelas sesuai tahun pelajaran yang dipilih
        $namaKelas = $row->siswa
            ?->siswaKelas
            ->firstWhere('tahun_pelajaran_id', $tahunId)
            ?->kelas
            ?->nama ?? '-';

        return [
            $this->rowNumber,
            $row->kode_bayar,
            $row->tanggal_bayar->format('d/m/Y'),
            $row->siswa->id_siswa ?? '-',
            $row->siswa->nama     ?? '-',
            $namaKelas,                             // ← pakai yang sudah difilter
            $row->siswa->jenjang  ?? '-',
            $row->bulan_label,
            $row->jumlah_bulan,
            (int) $row->nominal_per_bulan,
            (int) $row->nominal_donator,
            (int) $row->nominal_mamin,
            (int) ($row->kredit_digunakan ?? 0),
            (int) $row->total_bayar,
            $row->user->name ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Format kolom J, K, L, M, N sebagai angka ribuan
        foreach (['J', 'K', 'L', 'M', 'N'] as $col) {
            $sheet->getStyle($col . '2:' . $col . $sheet->getHighestRow())
                ->getNumberFormat()
                ->setFormatCode('#,##0');
        }

        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1B4B8A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
