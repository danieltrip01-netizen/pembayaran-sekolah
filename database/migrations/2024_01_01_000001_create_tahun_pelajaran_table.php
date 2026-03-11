<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tahun_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 9)->unique(); 
            // contoh: 2025/2026

            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');

            $table->boolean('is_active')->default(false);
            // hanya satu tahun yang aktif

            $table->boolean('is_locked')->default(false);
            // kalau sudah selesai bisa dikunci (tidak bisa edit)

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tahun_pelajaran');
    }
};