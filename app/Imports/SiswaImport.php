<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\SiswaKelas;
use App\Models\TahunPelajaran;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Facades\DB;

class SiswaImport implements
    ToCollection,
    WithHeadingRow,
    WithValidation,
    SkipsOnFailure,
    WithChunkReading
{
    use SkipsFailures;

    // ── Mode import ───────────────────────────────────────────────────
    // 'new'    → tambah siswa baru (perilaku lama)
    // 'update' → update siswa_kelas berdasarkan id_siswa yang sudah ada
    //            (untuk persiapan tahun pelajaran baru)
    private string $mode;

    private int   $countImported = 0;  // berhasil diproses
    private int   $countSkipped  = 0;  // dilewati/gagal
    private array $errorRows     = [];

    private TahunPelajaran $tahunPelajaran;

    /**
     * Cache ID siswa yang sudah dibuat dalam satu sesi import,
     * agar tidak ada duplikat saat batch.
     */
    private array $lastNum = [];
    private array $usedIds = [];

    /**
     * Cache tabel kelas: ['TK|KB' => Kelas, 'SD|I' => Kelas, ...]
     */
    private Collection $kelasByKey;

    public function __construct(
        private ?string $userJenjang = null,
        string $mode = 'new'
    ) {
        $this->mode = in_array($mode, ['new', 'update']) ? $mode : 'new';

        // Ambil tahun pelajaran aktif — wajib ada
        $this->tahunPelajaran = TahunPelajaran::aktif()
            ?? throw new \RuntimeException('Tidak ada tahun pelajaran aktif. Aktifkan tahun pelajaran terlebih dahulu.');

        // Pre-load semua kelas ke memory agar tidak N+1 per baris
        $this->kelasByKey = Kelas::all()->keyBy(
            fn(Kelas $k) => $k->jenjang . '|' . strtoupper(trim($k->nama))
        );

        // Pre-load counter ID siswa terakhir per jenjang (hanya untuk mode 'new')
        if ($this->mode === 'new') {
            foreach (['TK', 'SD', 'SMP'] as $j) {
                $prefix = $this->prefixFor($j);
                $last   = Siswa::withTrashed()
                    ->where('id_siswa', 'like', $prefix . '%')
                    ->orderByRaw('CAST(SUBSTRING(id_siswa, ' . (strlen($prefix) + 1) . ') AS UNSIGNED) DESC')
                    ->value('id_siswa');

                $this->lastNum[$j] = $last ? (int) substr($last, strlen($prefix)) : 0;
            }
        }
    }

    /**
     * Proses satu chunk baris sekaligus dalam satu transaksi.
     * Menggunakan ToCollection agar kita punya kontrol penuh (upsert, skip, log).
     */
    public function collection(Collection $rows): void
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $index => $row) {
                $this->processRow($row->toArray(), $index + 2); // +2: baris 1 = heading
            }
        });
    }

    private function processRow(array $row, int $rowNum): void
    {
        $nama    = trim($row['nama'] ?? '');
        $jenjang = strtoupper(trim($row['jenjang'] ?? ''));

        if ($nama === '') {
            return; // baris kosong, skip diam-diam
        }

        if (!in_array($jenjang, ['TK', 'SD', 'SMP'])) {
            $this->logError($rowNum, $nama, 'Jenjang tidak valid. Harus TK, SD, atau SMP.');
            return;
        }

        // Filter jenjang sesuai role admin yang login
        if ($this->userJenjang && $jenjang !== $this->userJenjang) {
            $this->countSkipped++;
            return;
        }

        // Lookup kelas
        $kelasNama = strtoupper(trim($row['kelas'] ?? ''));
        $kelas     = $this->kelasByKey->get($jenjang . '|' . $kelasNama);

        if (!$kelas) {
            $this->logError($rowNum, $nama, "Kelas '{$kelasNama}' tidak ditemukan untuk jenjang {$jenjang}.");
            return;
        }

        $nominalSpp     = $this->parseNominal($row['nominal_spp']     ?? $row['nominal_pembayaran'] ?? 0);
        $nominalDonator = $this->parseNominal($row['nominal_donator'] ?? 0);
        $nominalMamin   = $jenjang === 'TK'
                            ? $this->parseNominal($row['nominal_mamin'] ?? 0)
                            : 0;

        // ── MODE: UPDATE (re-import untuk tahun pelajaran baru) ──────
        if ($this->mode === 'update') {
            $idSiswa = trim($row['id_siswa'] ?? '');
            if ($idSiswa === '') {
                $this->logError($rowNum, $nama, 'Kolom id_siswa wajib diisi pada mode update.');
                return;
            }

            $siswa = Siswa::where('id_siswa', $idSiswa)->first();
            if (!$siswa) {
                $this->logError($rowNum, $nama, "Siswa dengan ID '{$idSiswa}' tidak ditemukan di database.");
                return;
            }

            // Update data dasar siswa jika ada perubahan
            $siswa->update([
                'nama'    => $nama,
                'jenjang' => $jenjang,
                'status'  => $row['status'] ?? $siswa->status,
                'tanggal_masuk' => $row['tanggal_masuk'] ?? $this->awalTahunAjaran(),
            ]);

            // Buat atau update record siswa_kelas untuk tahun pelajaran aktif
            SiswaKelas::updateOrCreate(
                [
                    'siswa_id'           => $siswa->id,
                    'tahun_pelajaran_id' => $this->tahunPelajaran->id,
                ],
                [
                    'kelas_id'        => $kelas->id,
                    'nominal_spp'     => $nominalSpp,
                    'nominal_donator' => $nominalDonator,
                    'nominal_mamin'   => $nominalMamin,
                ]
            );

            $this->countImported++;
            return;
        }

        // ── MODE: NEW (tambah siswa baru) ────────────────────────────
        // Cek duplikat nama + jenjang supaya tidak double import
        $sudahAda = Siswa::where('nama', $nama)
            ->where('jenjang', $jenjang)
            ->exists();

        if ($sudahAda) {
            $this->logError($rowNum, $nama, "Siswa dengan nama ini sudah ada di jenjang {$jenjang}.");
            return;
        }

        $siswa = Siswa::create([
            'id_siswa'       => $this->makeId($jenjang),
            'nama'           => $nama,
            'jenjang'        => $jenjang,
            'tanggal_masuk'  => $this->awalTahunAjaran(),
            'tanggal_keluar' => null,
            'status'         => 'aktif',
            'keterangan'     => 'Imported via Excel',
            'saldo_kredit'   => 0,
        ]);

        SiswaKelas::create([
            'siswa_id'           => $siswa->id,
            'kelas_id'           => $kelas->id,
            'tahun_pelajaran_id' => $this->tahunPelajaran->id,
            'nominal_spp'        => $nominalSpp,
            'nominal_donator'    => $nominalDonator,
            'nominal_mamin'      => $nominalMamin,
        ]);

        $this->countImported++;
    }

    // ── Validation ───────────────────────────────────────────────────

    public function rules(): array
    {
        $updateRules = $this->mode === 'update'
            ? ['*.id_siswa' => ['required', 'string']]
            : [];

        return array_merge($updateRules, [
            '*.nama'        => ['required', 'string', 'max:100'],
            '*.kelas'       => ['required', 'string', 'max:20'],
            '*.jenjang'     => ['nullable', 'in:TK,SD,SMP,tk,sd,smp'],
            '*.nominal_spp' => ['nullable', 'numeric', 'min:0'],
            // Kolom lama 'nominal_pembayaran' tetap diterima agar template lama tidak error
            '*.nominal_pembayaran' => ['nullable', 'numeric', 'min:0'],
            '*.nominal_donator'    => ['nullable', 'numeric', 'min:0'],
            '*.nominal_mamin'      => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    public function customValidationMessages(): array
    {
        return [
            '*.id_siswa.required' => 'Kolom ID_SISWA wajib diisi pada mode update.',
            '*.nama.required'     => 'Kolom NAMA tidak boleh kosong.',
            '*.kelas.required'    => 'Kolom KELAS tidak boleh kosong.',
            '*.jenjang.in'        => 'JENJANG harus TK, SD, atau SMP.',
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

    // ── Helpers ──────────────────────────────────────────────────────

    private function logError(int $row, string $nama, string $pesan): void
    {
        $this->errorRows[]  = compact('row', 'nama', 'pesan');
        $this->countSkipped++;
    }

    private function makeId(string $jenjang): string
    {
        $prefix = $this->prefixFor($jenjang);

        do {
            $this->lastNum[$jenjang]++;
            $id = $prefix . str_pad($this->lastNum[$jenjang], 4, '0', STR_PAD_LEFT);
        } while (in_array($id, $this->usedIds, true));

        $this->usedIds[] = $id;
        return $id;
    }

    private function prefixFor(string $jenjang): string
    {
        return match ($jenjang) {
            'TK'    => 'TK',
            'SD'    => 'SD',
            'SMP'   => 'SM',
            default => 'XX',
        };
    }

    private function awalTahunAjaran(): string
    {
        $tahun = (int) date('m') >= 7 ? (int) date('Y') : (int) date('Y') - 2;
        return $tahun . '-07-01';
    }

    private function parseNominal(mixed $value): float
    {
        if (is_numeric($value)) return (float) $value;
        return (float) preg_replace('/[^\d]/', '', (string) $value);
    }

    public function chunkSize(): int  { return 250; }

    public function getCountImported(): int { return $this->countImported; }
    public function getCountSkipped(): int  { return $this->countSkipped;  }
    public function getErrorRows(): array   { return $this->errorRows;     }
    public function getMode(): string       { return $this->mode;          }
}