<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setoran', function (Blueprint $table) {
            $table->id();
            $table->string('kode_setoran', 20)->unique();
            $table->date('tanggal_setoran');
            $table->enum('jenjang', ['TK', 'SD', 'SMP']);

            // POIN 5: Tambah relasi ke tahun_pelajaran
            // agar laporan setoran bisa difilter per tahun ajaran
            $table->foreignId('tahun_pelajaran_id')
                  ->constrained('tahun_pelajaran')
                  ->restrictOnDelete(); // jangan hapus tahun pelajaran jika ada setoran

            $table->decimal('total_nominal', 12, 2)->default(0);
            $table->decimal('total_mamin', 12, 2)->default(0);
            $table->decimal('total_keseluruhan', 12, 2)->default(0);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('keterangan')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setoran');
    }
};