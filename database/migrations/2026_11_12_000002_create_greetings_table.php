<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sambutan pembina untuk halaman Profil. Section di halaman publik
     * hanya tampil bila ada satu sambutan bertanda aktif; tanpa sambutan
     * aktif section otomatis tersembunyi.
     */
    public function up(): void
    {
        Schema::create('greetings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('role');
            $table->text('body');
            $table->foreignId('site_media_id')->nullable()->constrained('site_media')->cascadeOnDelete();
            $table->boolean('is_active')->default(false)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('greetings');
    }
};
