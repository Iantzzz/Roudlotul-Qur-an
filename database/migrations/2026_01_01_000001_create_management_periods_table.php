<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Periode kepengurusan. Struktur asrama berganti tiap tahun, sehingga
     * setiap tahun punya baris di sini dan riwayat lama tidak hilang.
     * Persis satu periode bertanda aktif dan hanya itu yang tampil publik.
     */
    public function up(): void
    {
        Schema::create('management_periods', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->boolean('is_active')->default(false)->index();
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('management_periods');
    }
};
