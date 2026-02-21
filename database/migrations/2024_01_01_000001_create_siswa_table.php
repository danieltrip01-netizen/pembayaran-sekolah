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
            $table->string('id_siswa', 20)->unique();
            $table->string('nama', 100);
            $table->string('kelas', 10); // I, II, III, IV, V, VI, VII, VIII, IX, A, B
            $table->enum('jenjang', ['TK', 'SD', 'SMP']);
            $table->decimal('nominal_pembayaran', 12, 2)->default(0);
            $table->decimal('nominal_donator', 12, 2)->default(0);
            $table->decimal('nominal_mamin', 12, 2)->default(0)->comment('Khusus TK');
            $table->date('tanggal_masuk');
            $table->date('tanggal_keluar')->nullable();
            $table->enum('status', ['aktif', 'tidak_aktif'])->default('aktif');
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