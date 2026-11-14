<?php

namespace App\Models;

use App\Models\Concerns\HasSiteMediaImage;
use App\Support\SiteMediaUploader;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Sambutan pembina untuk halaman Profil. Tanpa sambutan bertanda aktif,
 * section sambutan otomatis tersembunyi di halaman publik. Persis satu
 * sambutan bertanda aktif — mengaktifkan satu menonaktifkan yang lain.
 */
#[Fillable(['name', 'role', 'body', 'site_media_id', 'is_active'])]
class Greeting extends Model
{
    use HasSiteMediaImage;

    protected static function booted(): void
    {
        static::saved(function (self $greeting): void {
            if (! $greeting->is_active) {
                return;
            }

            static::withoutEvents(fn () => static::query()
                ->whereKeyNot($greeting->getKey())
                ->where('is_active', true)
                ->update(['is_active' => false]));
        });

        // Menghapus sambutan melepas satu-satunya rujukan fotonya;
        // rapikan baris media agar tidak menumpuk sebagai yatim.
        static::deleted(function (self $greeting): void {
            SiteMediaUploader::deleteIfOrphan($greeting->site_media_id);
        });
    }

    /**
     * Sambutan yang tampil di halaman publik.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
