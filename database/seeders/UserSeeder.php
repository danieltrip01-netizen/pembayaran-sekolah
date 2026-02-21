<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'         => 'Admin Yayasan',
                'nama_lengkap' => 'Administrator Yayasan',
                'email'        => 'admin@yayasan.sch.id',
                'password'     => Hash::make('password'),
                'role'         => 'admin_yayasan',
            ],
            [
                'name'         => 'Admin TK',
                'nama_lengkap' => 'Administrator TK/PAUD',
                'email'        => 'admin.tk@yayasan.sch.id',
                'password'     => Hash::make('password'),
                'role'         => 'admin_tk',
            ],
            [
                'name'         => 'Admin SD',
                'nama_lengkap' => 'Administrator SD',
                'email'        => 'admin.sd@yayasan.sch.id',
                'password'     => Hash::make('password'),
                'role'         => 'admin_sd',
            ],
            [
                'name'         => 'Admin SMP',
                'nama_lengkap' => 'Administrator SMP',
                'email'        => 'admin.smp@yayasan.sch.id',
                'password'     => Hash::make('password'),
                'role'         => 'admin_smp',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(['email' => $user['email']], $user);
        }
    }
}