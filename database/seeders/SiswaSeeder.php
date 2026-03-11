<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\SiswaKelas;
use App\Models\TahunPelajaran;

class SiswaSeeder extends Seeder
{
    public function run(): void
    {
        // ── Pastikan dependensi sudah ada ────────────────────────────
        $tahunAktif = TahunPelajaran::aktif();

        if (!$tahunAktif) {
            $this->command->warn('⚠️  Tidak ada tahun pelajaran aktif. Jalankan TahunPelajaranSeeder terlebih dahulu.');
            $this->command->warn('   php artisan db:seed --class=TahunPelajaranSeeder');
            return;
        }

        $kelasByNama = Kelas::all()->keyBy(fn($k) => $k->jenjang . '|' . $k->nama);

        if ($kelasByNama->isEmpty()) {
            $this->command->warn('⚠️  Tabel kelas masih kosong. Jalankan KelasSeeder terlebih dahulu.');
            $this->command->warn('   php artisan db:seed --class=KelasSeeder');
            return;
        }

        // ── Data siswa sample ────────────────────────────────────────
        // DIPERBAIKI: field lama 'kelas', 'nominal_pembayaran' dihapus.
        // Nominal SPP kini disimpan di tabel siswa_kelas, bukan di tabel siswa.
        $dataSiswa = [
            // TK
            [
                'id_siswa'      => 'TK0001',
                'nama'          => 'Siti Aisyah',
                'jenjang'       => 'TK',
                'kelas_nama'    => 'OA',        // untuk lookup tabel kelas
                'tanggal_masuk' => '2025-07-01',
                'status'        => 'aktif',
                'keterangan'    => null,
                'nominal_spp'      => 200000,
                'nominal_donator'  => 50000,
                'nominal_mamin'    => 15000,    // khusus TK
            ],
            [
                'id_siswa'      => 'TK0002',
                'nama'          => 'Ahmad Fauzi',
                'jenjang'       => 'TK',
                'kelas_nama'    => 'OB',
                'tanggal_masuk' => '2025-07-01',
                'status'        => 'aktif',
                'keterangan'    => null,
                'nominal_spp'      => 200000,
                'nominal_donator'  => 0,
                'nominal_mamin'    => 15000,
            ],
            // SD
            [
                'id_siswa'      => 'SD0001',
                'nama'          => 'Budi Santoso',
                'jenjang'       => 'SD',
                'kelas_nama'    => 'I',
                'tanggal_masuk' => '2025-07-01',
                'status'        => 'aktif',
                'keterangan'    => null,
                'nominal_spp'      => 300000,
                'nominal_donator'  => 75000,
                'nominal_mamin'    => 0,
            ],
            [
                'id_siswa'      => 'SD0002',
                'nama'          => 'Dewi Rahayu',
                'jenjang'       => 'SD',
                'kelas_nama'    => 'III',
                'tanggal_masuk' => '2025-07-01',
                'status'        => 'aktif',
                'keterangan'    => null,
                'nominal_spp'      => 300000,
                'nominal_donator'  => 100000,
                'nominal_mamin'    => 0,
            ],
            // SMP
            [
                'id_siswa'      => 'SM0001',
                'nama'          => 'Rizky Pratama',
                'jenjang'       => 'SMP',
                'kelas_nama'    => 'VII',
                'tanggal_masuk' => '2025-07-01',
                'status'        => 'aktif',
                'keterangan'    => null,
                'nominal_spp'      => 400000,
                'nominal_donator'  => 100000,
                'nominal_mamin'    => 0,
            ],
            [
                'id_siswa'      => 'SM0002',
                'nama'          => 'Nur Hidayah',
                'jenjang'       => 'SMP',
                'kelas_nama'    => 'IX',
                'tanggal_masuk' => '2025-07-01',
                'status'        => 'aktif',
                'keterangan'    => null,
                'nominal_spp'      => 400000,
                'nominal_donator'  => 0,
                'nominal_mamin'    => 0,
            ],
        ];

        $berhasil = 0;
        $skip     = 0;

        foreach ($dataSiswa as $row) {
            // Pisah data siswa dari data kelas/nominal
            $kelasNama      = $row['kelas_nama'];
            $nominalSpp     = $row['nominal_spp'];
            $nominalDonator = $row['nominal_donator'];
            $nominalMamin   = $row['nominal_mamin'];

            // Cari kelas yang sesuai jenjang + nama
            $kelas = $kelasByNama->get($row['jenjang'] . '|' . $kelasNama);

            if (!$kelas) {
                $this->command->warn("   ⚠️  Kelas '{$kelasNama}' ({$row['jenjang']}) tidak ditemukan, siswa {$row['id_siswa']} di-skip.");
                $skip++;
                continue;
            }

            // Simpan/update data siswa (tanpa field kelas/nominal lama)
            $siswa = Siswa::updateOrCreate(
                ['id_siswa' => $row['id_siswa']],
                [
                    'nama'           => $row['nama'],
                    'jenjang'        => $row['jenjang'],
                    'tanggal_masuk'  => $row['tanggal_masuk'],
                    'tanggal_keluar' => null,
                    'status'         => $row['status'],
                    'keterangan'     => $row['keterangan'],
                    'saldo_kredit'   => 0,
                ]
            );

            // Buat/update record siswa_kelas untuk tahun pelajaran aktif
            SiswaKelas::updateOrCreate(
                [
                    'siswa_id'           => $siswa->id,
                    'tahun_pelajaran_id' => $tahunAktif->id,
                ],
                [
                    'kelas_id'        => $kelas->id,
                    'nominal_spp'     => $nominalSpp,
                    'nominal_donator' => $nominalDonator,
                    // nominal_mamin hanya untuk TK
                    'nominal_mamin'   => $row['jenjang'] === 'TK' ? $nominalMamin : 0,
                ]
            );

            $berhasil++;
        }

        $this->command->info("✅ SiswaSeeder selesai: {$berhasil} siswa berhasil, {$skip} di-skip.");
        $this->command->info("   → Tahun pelajaran: {$tahunAktif->nama}");
    }
}