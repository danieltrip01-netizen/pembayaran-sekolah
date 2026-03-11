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
        return Pembayaran::with(['siswa', 'user', 'pembayaranBulan', 'siswaKelas.kelas'])

            // FIX: filter tahun pelajaran (wajib, defaultnya sudah di-handle controller)
            ->when(!empty($this->filter['tahun_pelajaran_id']), fn($q) =>
                $q->where('tahun_pelajaran_id', $this->filter['tahun_pelajaran_id'])
            )

            // Filter jenjang
            ->when(!empty($this->filter['jenjang']), fn($q) =>
                $q->whereHas('siswa', fn($sq) =>
                    $sq->where('jenjang', $this->filter['jenjang'])
                )
            )

            // FIX: filter kelas lewat siswa_kelas → kelas, bukan kolom kelas di tabel siswa
            ->when(!empty($this->filter['kelas']), fn($q) =>
                $q->whereHas('siswaKelas.kelas', fn($kq) =>
                    $kq->where('nama', $this->filter['kelas'])
                )
            )

            // FIX: filter bulan menggunakan relasi pembayaran_bulan,
            //      bukan whereJsonContains('bulan_bayar') yang sudah usang
            ->when(!empty($this->filter['bulan']), fn($q) =>
                $q->whereHas('pembayaranBulan', fn($bq) =>
                    $bq->where('bulan', $this->filter['bulan'])
                )
            )

            // Filter tanggal (opsional, dari LaporanController)
            ->when(!empty($this->filter['tanggal_dari']), fn($q) =>
                $q->where('tanggal_bayar', '>=', $this->filter['tanggal_dari'])
            )
            ->when(!empty($this->filter['tanggal_sampai']), fn($q) =>
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

        return [
            $this->rowNumber,
            $row->kode_bayar,
            $row->tanggal_bayar->format('d/m/Y'),
            $row->siswa->id_siswa ?? '-',
            $row->siswa->nama     ?? '-',

            // FIX: kelas dari siswaKelas snapshot (bukan $row->siswa->kelas yang sudah dihapus)
            $row->siswaKelas?->kelas?->nama ?? '-',

            $row->siswa->jenjang  ?? '-',

            // FIX: bulan_label sudah menggunakan relasi pembayaranBulan (sudah di-eager-load)
            $row->bulan_label,

            $row->jumlah_bulan,
            number_format($row->nominal_per_bulan,              0, ',', '.'),
            number_format($row->nominal_donator,                0, ',', '.'),
            number_format($row->nominal_mamin,                  0, ',', '.'),
            number_format($row->kredit_digunakan ?? 0,          0, ',', '.'),
            number_format($row->total_bayar,                    0, ',', '.'),
            $row->user->name ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1B4B8A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}