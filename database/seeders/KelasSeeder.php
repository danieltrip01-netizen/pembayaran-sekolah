<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KelasSeeder extends Seeder
{
    public function run(): void
    {
        // Matikan FK check sementara agar TRUNCATE tidak ditolak MySQL
        // karena tabel siswa_kelas punya foreign key ke tabel kelas.
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('kelas')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $kelas = [
            // ── TK / PAUD ──────────────────────────────────────────
            ['jenjang' => 'TK', 'nama' => 'KB',   'urutan' => 1],
            ['jenjang' => 'TK', 'nama' => 'OA',   'urutan' => 2],
            ['jenjang' => 'TK', 'nama' => 'OB',   'urutan' => 3],

            // ── SD ─────────────────────────────────────────────────
            ['jenjang' => 'SD', 'nama' => 'I',    'urutan' => 1],
            ['jenjang' => 'SD', 'nama' => 'II',   'urutan' => 2],
            ['jenjang' => 'SD', 'nama' => 'III',  'urutan' => 3],
            ['jenjang' => 'SD', 'nama' => 'IV',   'urutan' => 4],
            ['jenjang' => 'SD', 'nama' => 'V',    'urutan' => 5],
            ['jenjang' => 'SD', 'nama' => 'VI',   'urutan' => 6],

            // ── SMP ────────────────────────────────────────────────
            ['jenjang' => 'SMP', 'nama' => 'VII',  'urutan' => 1],
            ['jenjang' => 'SMP', 'nama' => 'VIII', 'urutan' => 2],
            ['jenjang' => 'SMP', 'nama' => 'IX',   'urutan' => 3],
        ];

        $now = now();
        foreach ($kelas as &$row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }

        DB::table('kelas')->insert($kelas);

        $this->command->info('✅ Tabel kelas berhasil diisi: ' . count($kelas) . ' baris.');
    }
}