<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();

            // NIS / ID unik siswa
            $table->string('id_siswa', 20)->unique();

            $table->string('nama', 100);

            // Jenjang tetap boleh di sini
            $table->enum('jenjang', ['TK', 'SD', 'SMP']);

            // Saldo kredit untuk kelebihan bayar
            $table->decimal('saldo_kredit', 12, 2)->default(0);

            $table->date('tanggal_masuk');
            $table->date('tanggal_keluar')->nullable();

            $table->enum('status', ['aktif', 'tidak_aktif'])
                  ->default('aktif');

            $table->text('keterangan')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};