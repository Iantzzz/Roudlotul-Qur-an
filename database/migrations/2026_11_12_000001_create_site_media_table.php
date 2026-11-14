<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Penyimpanan foto hasil unggahan panel admin. Filesystem Vercel
     * bersifat ephemeral (dibersihkan tiap deploy), sehingga foto yang
     * diunggah admin wajib disimpan di database — biner di-encode base64
     * agar aman disimpan sebagai teks lintas driver (pgsql/sqlite).
     */
    public function up(): void
    {
        Schema::create('site_media', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mime');
            $table->text('data');
            $table->unsignedInteger('size');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_media');
    }
};
