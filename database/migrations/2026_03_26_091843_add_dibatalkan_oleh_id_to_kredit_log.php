<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kredit_log', function (Blueprint $table) {
            $table->foreignId('dibatalkan_oleh_id')
                ->nullable()->constrained('users')->nullOnDelete()
                ->after('keterangan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kredit_log', function (Blueprint $table) {
            //
        });
    }
};
