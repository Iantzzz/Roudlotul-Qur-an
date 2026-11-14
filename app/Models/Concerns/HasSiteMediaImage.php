<?php

namespace App\Models\Concerns;

use App\Models\SiteMedia;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Konten yang mengaitkan satu gambar: hasil unggahan admin (site_media)
 * atau gambar bawaan situs (kunci pada config('site.images')). Accessor
 * image_url dipakai panel admin untuk kolom pratinjau; komponen gambar
 * publik membaca pasangan site_media_id/image_key secara langsung.
 */
trait HasSiteMediaImage
{
    public function siteMedia(): BelongsTo
    {
        return $this->belongsTo(SiteMedia::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if ($this->site_media_id) {
            return route('media.show', $this->site_media_id);
        }

        $image = $this->image_key ? config("site.images.{$this->image_key}") : null;

        return is_array($image) ? asset($image['path']) : null;
    }
}
