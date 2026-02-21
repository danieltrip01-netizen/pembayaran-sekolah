<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->string('kode_bayar', 20)->unique();
            $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');
            $table->foreignId('setoran_id')->nullable()->constrained('setoran')->onDelete('set null');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('tanggal_bayar');
            $table->json('bulan_bayar'); // ["2024-07", "2024-08"]
            $table->integer('jumlah_bulan');
            $table->decimal('nominal_per_bulan', 12, 2);
            $table->decimal('nominal_mamin', 12, 2)->default(0);
            $table->decimal('nominal_donator', 12, 2)->default(0);
            $table->decimal('total_bayar', 12, 2);
            $table->enum('status', ['lunas', 'sebagian'])->default('lunas');
            $table->text('keterangan')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};