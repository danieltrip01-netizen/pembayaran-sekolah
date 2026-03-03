<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom saldo_kredit ke tabel siswa
        Schema::table('siswa', function (Blueprint $table) {
            $table->unsignedInteger('saldo_kredit')->default(0)->after('nominal_mamin');
        });

        // 2. Buat tabel log kredit untuk audit trail
        Schema::create('kredit_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('pembayaran_id')->nullable()->constrained('pembayaran')->nullOnDelete();
            $table->enum('tipe', ['tambah', 'pakai']); // tambah = kredit masuk, pakai = kredit terpakai
            $table->unsignedInteger('jumlah');          // selalu positif
            $table->unsignedInteger('saldo_sebelum');
            $table->unsignedInteger('saldo_sesudah');
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kredit_log');
        Schema::table('siswa', function (Blueprint $table) {
            $table->dropColumn('saldo_kredit');
        });
    }
};