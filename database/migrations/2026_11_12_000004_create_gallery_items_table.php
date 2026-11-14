<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Butir galeri foto (halaman Galeri dan strip galeri di beranda).
     * Urutan (sort) mengatur tampilan; foto dapat berupa gambar bawaan
     * situs (image_key) atau unggahan admin (site_media_id).
     */
    public function up(): void
    {
        Schema::create('gallery_items', function (Blueprint $table) {
            $table->id();
            $table->string('caption');
            $table->string('image_key')->nullable();
            $table->foreignId('site_media_id')->nullable()->constrained('site_media')->cascadeOnDelete();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gallery_items');
    }
};
