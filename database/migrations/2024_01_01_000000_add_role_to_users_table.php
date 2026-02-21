<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin_yayasan', 'admin_tk', 'admin_sd', 'admin_smp'])
                  ->default('admin_sd')
                  ->after('email');
            $table->string('nama_lengkap')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'nama_lengkap']);
        });
    }
};