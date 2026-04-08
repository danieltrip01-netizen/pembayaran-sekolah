<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            // 'global' = data yayasan bersama, 'TK'/'SD'/'SMP' = per jenjang
            $table->string('jenjang', 10)->unique()->default('global');

            // ── Data bersama (hanya diisi di jenjang='global') ──────
            $table->string('nama_yayasan')->nullable();
            $table->string('alamat')->nullable();
            $table->string('kota')->nullable();
            $table->string('telepon')->nullable();
            $table->string('logo')->nullable();             // path file

            // ── Data per jenjang ────────────────────────────────────
            $table->string('nama_sekolah')->nullable();     // misal: "SD Kristen Dorkas"
            $table->string('nama_kepala_sekolah')->nullable();
            $table->string('nip_kepala_sekolah')->nullable();
            $table->string('nama_admin')->nullable();       // bendahara jenjang
            $table->string('tanda_tangan')->nullable();     // path file

            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};