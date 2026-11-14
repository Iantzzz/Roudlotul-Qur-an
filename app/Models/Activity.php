<?php

namespace App\Models;

use App\Models\Concerns\HasSiteMediaImage;
use App\Support\SiteMediaUploader;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Butir kegiatan: anggota satu kelompok pada halaman Kegiatan, atau kartu
 * beranda bila is_featured. Kegiatan tanpa kelompok tidak tampil di halaman
 * Kegiatan; nomor kartu dihalaman publik dihitung dari urutan, bukan disimpan.
 */
#[Fillable(['activity_group_id', 'label', 'title', 'text', 'image_key', 'site_media_id', 'sort', 'is_featured'])]
class Activity extends Model
{
    use HasSiteMediaImage;

    protected static function booted(): void
    {
        // Hapus kegiatan = lepas rujukan fotonya; rapikan media yatim.
        static::deleted(function (self $activity): void {
            SiteMediaUploader::deleteIfOrphan($activity->site_media_id);
        });
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ActivityGroup::class, 'activity_group_id');
    }

    /**
     * Kegiatan yang tampil sebagai kartu di beranda.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
