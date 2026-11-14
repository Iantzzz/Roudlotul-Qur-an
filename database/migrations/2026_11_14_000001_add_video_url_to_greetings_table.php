<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tautan video pembina (opsional) pada sambutan. Kolom baru bersifat
     * aditif: baris lama tetap valid tanpa video (NULL), dan halaman publik
     * hanya merender embed bila tautannya dikenali.
     */
    public function up(): void
    {
        Schema::table('greetings', function (Blueprint $table) {
            $table->string('video_url', 500)->nullable()->after('site_media_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('greetings', function (Blueprint $table) {
            $table->dropColumn('video_url');
        });
    }
};
