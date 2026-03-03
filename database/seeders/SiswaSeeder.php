<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Siswa;

class SiswaSeeder extends Seeder
{
    public function run(): void
    {
        $siswa = [
            // TK
            ['id_siswa' => 'TK0001', 'nama' => 'Siswa  1', 'kelas' => 'A', 'jenjang' => 'TK',
             'nominal_pembayaran' => 200000, 'nominal_donator' => 50000, 'nominal_mamin' => 5000,
             'tanggal_masuk' => '2024-07-01', 'status' => 'aktif'],
            // SD
            ['id_siswa' => 'SD0001', 'nama' => 'Siswa  2', 'kelas' => 'I', 'jenjang' => 'SD',
             'nominal_pembayaran' => 300000, 'nominal_donator' => 75000, 'nominal_mamin' => 0,
             'tanggal_masuk' => '2024-07-01', 'status' => 'aktif'],
            // SMP
            ['id_siswa' => 'SM0001', 'nama' => 'Siswa  3', 'kelas' => 'VII', 'jenjang' => 'SMP',
             'nominal_pembayaran' => 400000, 'nominal_donator' => 100000, 'nominal_mamin' => 0,
             'tanggal_masuk' => '2024-07-01', 'status' => 'aktif'],
        ];

        foreach ($siswa as $s) {
            Siswa::updateOrCreate(['id_siswa' => $s['id_siswa']], $s);
        }
    }
}