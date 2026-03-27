<?php

namespace App\Exports;

use App\Models\Siswa;
use App\Models\TahunPelajaran;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class SiswaExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths,
    WithTitle
{
    /**
     * Kelas tingkat akhir per jenjang.
     * Siswa yang duduk di kelas ini akan dikecualikan saat ekspor
     * dengan mode "untuk tahun baru" ($filterKelasAkhir = true).
     *
     * Sesuaikan nilai ini dengan nama kelas yang ada di tabel `kelas`.
     */
    private const KELAS_AKHIR = [
        'TK'  => ['OB'],
        'SD'  => ['VI'],
        'SMP' => ['IX'],
    ];

    private TahunPelajaran $tahunPelajaran;
    private int $rowCount = 0;

    /**
     * @param string|null         $jenjang          Filter jenjang user (null = semua)
     * @param TahunPelajaran|null $tahunPelajaran   Sumber data; null = tahun aktif
     * @param bool                $filterKelasAkhir true  -> siswa tingkat akhir tidak diekspor
     *                                              false -> semua siswa diekspor (default)
     */
    public function __construct(
        private ?string $jenjang = null,
        ?TahunPelajaran $tahunPelajaran = null,
        private bool $filterKelasAkhir = false,
    ) {
        $this->tahunPelajaran = $tahunPelajaran
            ?? TahunPelajaran::aktif()
            ?? throw new \RuntimeException('Tidak ada tahun pelajaran aktif.');
    }

    public function title(): string
    {
        return 'Data Siswa ' . $this->tahunPelajaran->nama;
    }

    /**
     * Query siswa berdasarkan tahun pelajaran yang dipilih.
     *
     * Jika $filterKelasAkhir aktif (ekspor "untuk tahun baru"),
     * siswa yang berada di kelas tingkat akhir dikecualikan sepenuhnya
     * karena dianggap sudah lulus dan tidak perlu didaftarkan ulang.
     */
    public function query()
    {
        $tahunId = $this->tahunPelajaran->id;

        // Ambil kelas akhir yang relevan.
        // Jika ada filter jenjang aktif, cukup ambil kelas akhir jenjang itu saja
        // agar tidak over-exclude (mis. "VI" jenjang lain yang kebetulan namanya sama).
        $kelasAkhir = $this->jenjang
            ? (self::KELAS_AKHIR[$this->jenjang] ?? [])
            : collect(self::KELAS_AKHIR)->flatten()->all();

        return Siswa::query()
            ->with([
                'siswakelas' => fn($q) => $q
                    ->where('tahun_pelajaran_id', $tahunId)
                    ->with('kelas'),
            ])
            ->whereHas('siswakelas', fn($q) => $q->where('tahun_pelajaran_id', $tahunId))
            ->when($this->jenjang, fn($q) => $q->where('jenjang', $this->jenjang))
            // Kecualikan siswa tingkat akhir jika flag aktif
            ->when(
                $this->filterKelasAkhir && count($kelasAkhir),
                fn($q) => $q->whereDoesntHave(
                    'siswakelas',
                    fn($q) => $q
                        ->where('tahun_pelajaran_id', $tahunId)
                        ->whereHas('kelas', fn($k) => $k->whereIn('nama', $kelasAkhir))
                )
            )
            ->orderBy('jenjang')
            ->orderBy('nama');
    }

    public function headings(): array
    {
        return [
            'id_siswa',         // A — JANGAN DIUBAH, dipakai saat re-import
            'nama',             // B — referensi, boleh diubah
            'jenjang',          // C — TK / SD / SMP
            'kelas',            // D — nama kelas (I, II, VII, KB, dll) editable
            'no_hp_wali',       // E — No. HP wali, editable
            'nominal_spp',      // F — editable
            'nominal_donator',  // G — editable
            'nominal_mamin',    // H — editable (hanya TK)
            'status',           // I — aktif / tidak_aktif
        ];
    }

    public function map($siswa): array
    {
        $this->rowCount++;
        $ka = $siswa->siswakelas->first();

        return [
            $siswa->id_siswa,
            $siswa->nama,
            $siswa->jenjang,
            $ka?->kelas?->nama ?? '',
            $siswa->no_hp_wali ?? '',
            (int) ($ka?->nominal_spp     ?? 0),
            (int) ($ka?->nominal_donator ?? 0),
            (int) ($ka?->nominal_mamin   ?? 0),
            $siswa->status,
        ];
    }

    public function styles(Worksheet $sheet): void
    {
        $lastRow = $this->rowCount + 1;

        // ── Heading ──────────────────────────────────────────────────
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size'  => 10,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1B4B8A'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // ── Kolom read-only: A-C, I ───────────────────────────────────
        $lockedStyle = [
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF1F5F9']],
            'font' => ['color' => ['argb' => 'FF64748B']],
        ];
        if ($lastRow > 1) {
            $sheet->getStyle("A2:C{$lastRow}")->applyFromArray($lockedStyle);
            $sheet->getStyle("I2:I{$lastRow}")->applyFromArray($lockedStyle);
        }

        // ── Kolom editable: D-H — kuning muda ────────────────────────
        if ($lastRow > 1) {
            $sheet->getStyle("D2:H{$lastRow}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFDE7']],
            ]);
        }

        // ── Border ───────────────────────────────────────────────────
        $sheet->getStyle("A1:I{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FFCBD5E1'],
                ],
            ],
        ]);

        $sheet->freezePane('A2');

        $sheet->getStyle('F1:H1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // ── Zebra stripe ──────────────────────────────────────────────
        for ($r = 2; $r <= $lastRow; $r++) {
            if ($r % 2 === 0) {
                $sheet->getStyle("A{$r}:I{$r}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
                ]);
            }
        }

        // ── Komentar heading ─────────────────────────────────────────
        $sheet->getComment('A1')->getText()
            ->createTextRun('Jangan ubah kolom ini. Dipakai saat re-import.');
        $sheet->getComment('D1')->getText()
            ->createTextRun('Isi dengan nama kelas baru untuk tahun ajaran ini.');
        $sheet->getComment('E1')->getText()
            ->createTextRun('No. HP wali siswa (contoh: 08123456789). Kosongkan jika tidak ada.');
        $sheet->getComment('F1')->getText()
            ->createTextRun('SPP per bulan (angka, tanpa Rp/titik/koma).');
        $sheet->getComment('G1')->getText()
            ->createTextRun('Keringanan SPP. Isi 0 jika tidak ada.');
        $sheet->getComment('H1')->getText()
            ->createTextRun('Makan & minum. Hanya relevan untuk TK, isi 0 untuk SD/SMP.');

        // ── Catatan kaki jika filter kelas akhir aktif ────────────────
        if ($this->filterKelasAkhir) {
            $noteRow = $lastRow + 2;
            $kelasAkhirStr = collect(self::KELAS_AKHIR)
                ->map(fn($kelas, $jenjang) => $jenjang . ': ' . implode(', ', $kelas))
                ->implode(' | ');
            $sheet->setCellValue(
                "A{$noteRow}",
                "Catatan: Siswa tingkat akhir tidak ditampilkan karena dianggap lulus ({$kelasAkhirStr})."
            );
            $sheet->getStyle("A{$noteRow}")->applyFromArray([
                'font' => ['italic' => true, 'color' => ['argb' => 'FF94A3B8'], 'size' => 9],
            ]);
            $sheet->mergeCells("A{$noteRow}:I{$noteRow}");
        }
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,  // id_siswa
            'B' => 28,  // nama
            'C' => 8,   // jenjang
            'D' => 10,  // kelas
            'E' => 20,  // no_hp_wali
            'F' => 18,  // nominal_spp
            'G' => 18,  // nominal_donator
            'H' => 16,  // nominal_mamin
            'I' => 14,  // status
        ];
    }

    public function getTahunPelajaran(): TahunPelajaran
    {
        return $this->tahunPelajaran;
    }

    /**
     * Kembalikan daftar kelas akhir per jenjang.
     * Berguna untuk ditampilkan sebagai info di UI.
     */
    public static function getKelasAkhir(): array
    {
        return self::KELAS_AKHIR;
    }
}