<?php

namespace App\Exports;

use App\Models\Pembayaran;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LaporanExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    private array $filter;
    private int $rowNumber = 0;

    public function __construct(array $filter)
    {
        $this->filter = $filter;
    }

    public function title(): string
    {
        return 'Laporan Pembayaran';
    }

    public function collection()
    {
        return Pembayaran::with(['siswa', 'user'])
            ->when($this->filter['jenjang'] ?? null, fn($q) => $q->whereHas('siswa', fn($sq) => $sq->where('jenjang', $this->filter['jenjang'])))
            ->when($this->filter['kelas'] ?? null,   fn($q) => $q->whereHas('siswa', fn($sq) => $sq->where('kelas', $this->filter['kelas'])))
            ->when($this->filter['bulan'] ?? null,   fn($q) => $q->whereJsonContains('bulan_bayar', $this->filter['bulan']))
            ->orderBy('tanggal_bayar')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Bayar',
            'Tanggal',
            'ID Siswa',
            'Nama Siswa',
            'Kelas',
            'Jenjang',
            'Bulan Dibayar',
            'Jumlah Bulan',
            'Nominal/Bulan',
            'Donatur',
            'Mamin',
            'Total',
            'Petugas',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;
        return [
            $this->rowNumber,
            $row->kode_bayar,
            $row->tanggal_bayar->format('d/m/Y'),
            $row->siswa->id_siswa ?? '-',
            $row->siswa->nama ?? '-',
            $row->siswa->kelas ?? '-',
            $row->siswa->jenjang ?? '-',
            $row->bulan_label,
            $row->jumlah_bulan,
            number_format($row->nominal_per_bulan, 0, ',', '.'),
            number_format($row->nominal_donator, 0, ',', '.'),
            number_format($row->nominal_mamin, 0, ',', '.'),
            number_format($row->total_bayar, 0, ',', '.'),
            $row->user->name ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1B4B8A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}