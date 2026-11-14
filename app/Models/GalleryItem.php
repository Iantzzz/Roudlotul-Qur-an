<?php

namespace App\Models;

use App\Models\Concerns\HasSiteMediaImage;
use App\Support\SiteMediaUploader;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Butir galeri foto: halaman Galeri dan strip galeri di beranda. Foto dapat
 * berupa gambar bawaan situs (image_key) atau unggahan admin (site_media_id).
 */
#[Fillable(['caption', 'image_key', 'site_media_id', 'sort'])]
class GalleryItem extends Model
{
    use HasSiteMediaImage;

    protected static function booted(): void
    {
        // Hapus foto galeri = lepas rujukan fotonya; rapikan media yatim.
        static::deleted(function (self $item): void {
            SiteMediaUploader::deleteIfOrphan($item->site_media_id);
        });
    }
}
