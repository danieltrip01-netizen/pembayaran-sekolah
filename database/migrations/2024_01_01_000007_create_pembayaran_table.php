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

            $table->foreignId('siswa_id')
                  ->constrained('siswa')
                  ->cascadeOnDelete();

            $table->foreignId('setoran_id')
                  ->nullable()
                  ->constrained('setoran')
                  ->nullOnDelete();

            $table->foreignId('tahun_pelajaran_id')
                  ->constrained('tahun_pelajaran')
                  ->restrictOnDelete();
            // Jangan hapus tahun pelajaran jika masih ada pembayaran terkait.
            // Tampilkan pesan error yang ramah di UI.

            $table->foreignId('siswa_kelas_id')
                  ->nullable()
                  ->constrained('siswa_kelas')
                  ->nullOnDelete();
            // Referensi ke record siswa_kelas yang menjadi sumber tarif saat transaksi.
            // Nullable agar data pembayaran lama tetap valid jika record siswa_kelas dihapus.
            // Snapshot nominal (nominal_per_bulan, dll) tetap tersimpan di tabel ini
            // sehingga histori tidak berubah walau tarif di siswa_kelas diupdate.

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->date('tanggal_bayar');

            // Detail bulan yang dibayar disimpan di tabel pembayaran_bulan (bawah)
            // agar bisa di-query, di-index, dan dicek duplikat per siswa per bulan.

            $table->integer('jumlah_bulan');

            // Snapshot tarif saat transaksi — tidak ikut berubah jika tarif diedit nanti
            $table->decimal('nominal_per_bulan', 12, 2);
            $table->decimal('nominal_mamin', 12, 2)->default(0);
            $table->decimal('nominal_donator', 12, 2)->default(0);

            $table->decimal('kredit_digunakan', 12, 2)->default(0);
            $table->decimal('total_bayar', 12, 2);

            $table->enum('status', ['lunas', 'sebagian'])->default('lunas');

            $table->decimal('sisa_tagihan', 12, 2)->default(0);
            // Nilai 0 jika status = 'lunas', > 0 jika status = 'sebagian'

            $table->text('keterangan')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Detail bulan yang dibayar per transaksi
        Schema::create('pembayaran_bulan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pembayaran_id')
                  ->constrained('pembayaran')
                  ->cascadeOnDelete();

            $table->foreignId('siswa_id')
                  ->constrained('siswa')
                  ->cascadeOnDelete();

            $table->string('bulan', 7);
            // Format: "2024-07" (YYYY-MM)
            // Dipisah agar bisa di-query: "siswa X sudah bayar bulan apa saja?"

            $table->timestamps();

            // Satu siswa tidak boleh bayar bulan yang sama dua kali
            $table->unique(['siswa_id', 'bulan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran_bulan');
        Schema::dropIfExists('pembayaran');
    }
};