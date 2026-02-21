<?php

namespace App\Imports;

use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Validators\Failure;

class SiswaImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnFailure,
    WithBatchInserts,
    WithChunkReading
{
    use SkipsFailures;

    private int   $countImported = 0;
    private int   $countSkipped  = 0;
    private array $errorRows     = [];

    /**
     * Cache angka terakhir per jenjang untuk generate ID secara in-memory.
     */
    private array $lastNum = [];
    private array $usedIds = [];

    public function __construct(private ?string $userJenjang = null)
    {
        // Inisialisasi counter ID dari Database satu kali saja di awal.
        foreach (['TK', 'SD', 'SMP'] as $j) {
            $prefix = match($j) {
                'TK'  => 'TK',
                'SD'  => 'SD',
                'SMP' => 'SM',
            };

            $last = Siswa::withTrashed()
                ->where('id_siswa', 'like', $prefix . '%')
                ->orderByRaw('CAST(SUBSTRING(id_siswa, ' . (strlen($prefix) + 1) . ') AS UNSIGNED) DESC')
                ->value('id_siswa');

            $this->lastNum[$j] = $last ? (int) substr($last, strlen($prefix)) : 0;
        }
    }

    /**
     * Logic pembuatan Model Siswa per baris Excel.
     */
    public function model(array $row): ?Siswa
    {
        $nama = trim($row['nama'] ?? '');
        if ($nama === '') {
            return null;
        }

        $jenjang = strtoupper(trim($row['jenjang'] ?? ''));
        if (!in_array($jenjang, ['TK', 'SD', 'SMP'])) {
            $jenjang = 'SD';
        }

        // Filter: Hanya proses jenjang yang sesuai dengan profil admin yang login.
        if ($this->userJenjang && $jenjang !== $this->userJenjang) {
            $this->countSkipped++;
            return null;
        }

        $this->countImported++;

        return new Siswa([
            'id_siswa'           => $this->makeId($jenjang),
            'nama'               => $nama,
            'kelas'              => strtoupper(trim($row['kelas'] ?? '')),
            'jenjang'            => $jenjang,
            'nominal_pembayaran' => $this->parseNominal($row['nominal_pembayaran'] ?? 0),
            'nominal_donator'    => $this->parseNominal($row['nominal_donator']    ?? 0),
            'nominal_mamin'      => $jenjang === 'TK' ? $this->parseNominal($row['nominal_mamin'] ?? 0) : 0,
            'tanggal_masuk'      => $this->awalTahunAjaran(),
            'tanggal_keluar'     => null,
            'status'             => 'aktif',
            'keterangan'         => 'Imported via Excel',
        ]);
    }

    private function makeId(string $jenjang): string
    {
        $prefix = match($jenjang) {
            'TK'    => 'TK',
            'SD'    => 'SD',
            'SMP'   => 'SM',
            default => 'XX',
        };

        do {
            $this->lastNum[$jenjang]++;
            $id = $prefix . str_pad($this->lastNum[$jenjang], 4, '0', STR_PAD_LEFT);
        } while (in_array($id, $this->usedIds, true));

        $this->usedIds[] = $id;
        return $id;
    }

    public function rules(): array
    {
        return [
            '*.nama'               => ['required', 'string', 'max:100'],
            '*.kelas'              => ['required', 'string', 'max:10'],
            '*.jenjang'            => ['nullable', 'in:TK,SD,SMP,tk,sd,smp'],
            '*.nominal_pembayaran' => ['nullable', 'numeric', 'min:0'],
            '*.nominal_donator'    => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            '*.nama.required'  => 'Kolom NAMA tidak boleh kosong.',
            '*.kelas.required' => 'Kolom KELAS tidak boleh kosong.',
            '*.jenjang.in'     => 'JENJANG harus TK, SD, atau SMP.',
        ];
    }

    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            $this->errorRows[] = [
                'row'   => $failure->row(),
                'nama'  => $failure->values()['nama'] ?? 'Kosong',
                'pesan' => implode(', ', $failure->errors()),
            ];
            $this->countSkipped++;
        }
    }

    public function batchSize(): int { return 100; }
    public function chunkSize(): int { return 250; }

    public function getCountImported(): int { return $this->countImported; }
    public function getCountSkipped(): int  { return $this->countSkipped;  }
    public function getErrorRows(): array   { return $this->errorRows;     }

    private function awalTahunAjaran(): string
    {
        $tahun = (int) date('m') >= 7 ? (int) date('Y') : (int) date('Y') - 1;
        return $tahun . '-07-01';
    }

    private function parseNominal(mixed $value): float
    {
        if (is_numeric($value)) return (float) $value;
        return (float) preg_replace('/[^\d]/', '', (string) $value);
    }
}