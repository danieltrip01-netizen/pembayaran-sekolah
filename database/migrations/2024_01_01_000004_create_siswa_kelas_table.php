<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel ini menjadi satu-satunya sumber kebenaran untuk:
     * - Penempatan siswa di kelas tertentu pada suatu tahun pelajaran
     * - Besaran SPP siswa tersebut di tahun pelajaran yang sama
     *
     * Alasan digabung (bukan tabel tarif_spp terpisah):
     * - Keduanya memiliki granularitas yang sama: (siswa_id, tahun_pelajaran_id)
     * - Menghindari duplikasi tabel dengan unique key yang identik
     * - Satu query JOIN sudah cukup untuk mendapat info kelas + tarif sekaligus
     */
    public function up(): void
    {
        Schema::create('siswa_kelas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('siswa_id')
                  ->constrained('siswa')
                  ->cascadeOnDelete();

            $table->foreignId('kelas_id')
                  ->constrained('kelas')
                  ->cascadeOnDelete();

            $table->foreignId('tahun_pelajaran_id')
                  ->constrained('tahun_pelajaran')
                  ->cascadeOnDelete();

            // ── Nominal SPP tahun ini ────────────────────────────────────────
            // Wajib diisi saat mendaftarkan siswa ke tahun pelajaran baru.
            // Nilai bisa berbeda antar siswa (ada yang dapat keringanan, dll).
            $table->decimal('nominal_spp', 12, 2)->default(0);

            // Donatur sukarela per bulan dari wali siswa. Default 0.
            $table->decimal('nominal_donator', 12, 2)->default(0);

            // Biaya makan & minum per bulan. Khusus TK, isi 0 untuk SD/SMP.
            $table->decimal('nominal_mamin', 12, 2)->default(0)
                  ->comment('Khusus TK, isi 0 untuk SD/SMP');
            // ────────────────────────────────────────────────────────────────

            $table->softDeletes();
            $table->timestamps();

            // 1 siswa hanya boleh punya 1 record per tahun pelajaran
            $table->unique(['siswa_id', 'tahun_pelajaran_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa_kelas');
    }
};