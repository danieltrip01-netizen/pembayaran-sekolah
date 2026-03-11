<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();

            $table->string('nama', 20);


            $table->enum('jenjang', ['TK', 'SD', 'SMP']);
            // Supaya bisa difilter sesuai jenjang

            $table->integer('urutan')->default(0);
            // Untuk sorting tampilan (1,2,3,...)

            $table->timestamps();

            // Supaya tidak ada kelas dobel
            $table->unique(['nama', 'jenjang']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};