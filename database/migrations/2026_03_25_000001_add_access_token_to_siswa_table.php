<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->string('access_token', 64)->nullable()->unique()->after('no_hp_wali');
        });

        // Isi token untuk semua siswa yang sudah ada
        DB::table('siswa')->whereNull('access_token')->lazyById()->each(function ($siswa) {
            DB::table('siswa')
                ->where('id', $siswa->id)
                ->update(['access_token' => Str::random(32)]);
        });
    }

    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->dropColumn('access_token');
        });
    }
};