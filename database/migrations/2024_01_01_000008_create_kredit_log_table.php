<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kredit_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('pembayaran_id')->nullable()->constrained('pembayaran')->nullOnDelete();
            $table->enum('tipe', ['tambah', 'pakai']);
            $table->decimal('jumlah', 12, 2);
            $table->decimal('saldo_sebelum', 12, 2);
            $table->decimal('saldo_sesudah', 12, 2);
            $table->string('keterangan', 255)->nullable();
            $table->softDeletes();  
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kredit_log');
    }
};