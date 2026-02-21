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

        // Seed 4 baris default
        $now = now();
        DB::table('settings')->insert([
            ['jenjang' => 'global', 'nama_yayasan' => '', 'created_at' => $now, 'updated_at' => $now],
            ['jenjang' => 'TK',     'nama_sekolah' => 'TK Kristen Dorkas',  'created_at' => $now, 'updated_at' => $now],
            ['jenjang' => 'SD',     'nama_sekolah' => 'SD Kristen Dorkas',  'created_at' => $now, 'updated_at' => $now],
            ['jenjang' => 'SMP',    'nama_sekolah' => 'SMP Kristen Dorkas', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};