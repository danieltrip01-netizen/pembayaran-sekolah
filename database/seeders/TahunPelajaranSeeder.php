<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TahunPelajaranSeeder extends Seeder
{
    public function run(): void
    {
        // Matikan FK check: tabel siswa_kelas, pembayaran, setoran
        // semuanya punya foreign key ke tahun_pelajaran.
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('tahun_pelajaran')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = [
            [
                'nama'            => '2025/2026',
                'tanggal_mulai'   => '2025-07-01',
                'tanggal_selesai' => '2026-06-30',
                'is_active'       => true,
                'is_locked'       => false,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]
        ];

        DB::table('tahun_pelajaran')->insert($data);

        $this->command->info('✅ Tabel tahun_pelajaran berhasil diisi: ' . count($data) . ' baris.');
        $this->command->info('   → Tahun aktif: 2025/2026');
    }
}