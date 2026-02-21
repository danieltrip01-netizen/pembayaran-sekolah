<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class SiswaTemplateExport implements
    FromArray,
    WithHeadings,
    WithStyles,
    WithColumnWidths,
    WithTitle
{
    public function title(): string
    {
        return 'Template Import Siswa';
    }

    public function headings(): array
    {
        return [
            'nama',
            'kelas',
            'jenjang',
            'nominal_pembayaran',
            'nominal_donator',
            'nominal_mamin',
        ];
    }

    public function array(): array
    {
        return [
            [
                'Budi Santoso',  // nama
                'I',             // kelas (A/B untuk TK | I–VI untuk SD | VII–IX untuk SMP)
                'SD',            // jenjang: TK / SD / SMP
                175000,          // nominal_pembayaran: SPP per bulan
                60000,           // nominal_donator: keringanan (0 jika tidak ada)
                0,               // nominal_mamin: hanya untuk TK, isi 0 untuk SD/SMP
            ],
            [
                'Sari Dewi',
                'A',
                'TK',
                200000,
                0,
                50000,
            ],
            [
                'Contoh Siswa Tiga',
                'II',
                'SD',
                175000,
                60000,
                0,
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = count($this->array()) + 1;

        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1B4B8A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            "A1:F{$lastRow}" => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['argb' => 'FFCCCCCC'],
                    ],
                ],
            ],
            2 => ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFF9C4']]],
            3 => ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFCE4EC']]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 28,  // nama
            'B' => 8,   // kelas
            'C' => 8,   // jenjang
            'D' => 22,  // nominal_pembayaran
            'E' => 18,  // nominal_donator
            'F' => 16,  // nominal_mamin
        ];
    }
}