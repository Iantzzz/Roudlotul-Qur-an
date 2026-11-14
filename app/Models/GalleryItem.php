<?php

namespace App\Models;

use App\Models\Concerns\HasSiteMediaImage;
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
}
