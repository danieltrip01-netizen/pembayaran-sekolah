<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            // Nomor HP wali murid untuk notifikasi WhatsApp
            // Ditempatkan setelah kolom 'nama' (sesuaikan jika perlu)
            $table->string('no_hp_wali', 20)->nullable()->after('nama')
                  ->comment('No HP wali murid untuk notifikasi WA');
        });
    }

    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->dropColumn('no_hp_wali');
        });
    }
};