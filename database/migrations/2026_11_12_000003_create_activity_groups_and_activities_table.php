<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kelompok kegiatan (bagian "Kelompok 01..04" halaman Kegiatan) beserta
     * butir kegiatannya. Kegiatan tanpa kelompok dan bertanda is_featured
     * menjadi kartu pada beranda; image_key merujuk gambar bawaan situs
     * (config site.images), site_media_id merujuk unggahan admin.
     */
    public function up(): void
    {
        Schema::create('activity_groups', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_group_id')->nullable()->constrained('activity_groups')->cascadeOnDelete();
            $table->string('label');
            $table->string('title');
            $table->text('text');
            $table->string('image_key')->nullable();
            $table->foreignId('site_media_id')->nullable()->constrained('site_media')->cascadeOnDelete();
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
        Schema::dropIfExists('activity_groups');
    }
};
