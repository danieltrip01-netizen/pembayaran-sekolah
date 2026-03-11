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
        return 'Template Import Siswa Baru';
    }

    /**
     * Heading kolom — harus cocok persis dengan yang dibaca SiswaImport.
     * PERBAIKAN: ganti 'nominal_pembayaran' → 'nominal_spp' sesuai skema baru.
     */
    public function headings(): array
    {
        return [
            'nama',             // A — wajib
            'jenjang',          // B — TK / SD / SMP
            'kelas',            // C — nama kelas (I, II, VII, KB, dll)
            'nominal_spp',      // D — SPP per bulan
            'nominal_donator',  // E — keringanan (0 jika tidak ada)
            'nominal_mamin',    // F — khusus TK, isi 0 untuk SD/SMP
        ];
    }

    public function array(): array
    {
        return [
            // Contoh SD
            ['Budi Santoso',  'SD',  'I',   175000, 60000, 0],
            // Contoh TK
            ['Sari Dewi',     'TK',  'OA',  200000, 0,     50000],
            // Contoh SMP
            ['Andi Pratama',  'SMP', 'VII', 350000, 75000, 0],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = count($this->array()) + 1;

        return [
            // Heading — navy bold
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 10],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1B4B8A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Border semua sel
            "A1:F{$lastRow}" => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['argb' => 'FFCCCCCC'],
                    ],
                ],
            ],
            // Contoh baris — warna berbeda agar mudah dibaca
            2 => ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFDE7']]],
            3 => ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFCE4EC']]],
            4 => ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF3E5F5']]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 28,  // nama
            'B' => 8,   // jenjang
            'C' => 8,   // kelas
            'D' => 18,  // nominal_spp
            'E' => 18,  // nominal_donator
            'F' => 16,  // nominal_mamin
        ];
    }
}