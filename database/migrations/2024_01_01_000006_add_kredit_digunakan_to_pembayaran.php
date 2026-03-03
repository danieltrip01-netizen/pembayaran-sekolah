<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            // Berapa besar kredit yang dipotong dalam transaksi ini
            $table->unsignedInteger('kredit_digunakan')->default(0)->after('nominal_mamin');
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropColumn('kredit_digunakan');
        });
    }
};