<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Anggota kepengurusan untuk satu periode: jabatan diketik bebas
     * (Pembina, Ketua, dst.) dengan urutan tampil manual agar tetap
     * sederhana saat susunan jabatan berubah antarperiode.
     */
    public function up(): void
    {
        Schema::create('management_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('management_period_id')->constrained()->cascadeOnDelete();
            $table->string('jabatan');
            $table->string('nama');
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('management_members');
    }
};
