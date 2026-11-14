<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Kelompok kegiatan pada halaman Kegiatan (bagian "Kelompok 01..04").
 * Slug dipakai sebagai anchor section (#ibadah, #pendidikan, ...) dan
 * dibentuk otomatis dari judul bila admin tidak mengisinya.
 */
#[Fillable(['title', 'slug', 'description', 'sort'])]
class ActivityGroup extends Model
{
    protected static function booted(): void
    {
        static::creating(function (self $group): void {
            // Pakai slug yang diisi admin bila ada; selain itu bentuk dari
            // judul. Benturan dengan slug lain diberi akhiran angka berurut
            // agar kontrak unik di migration selalu terpenuhi.
            $base = trim((string) $group->slug) ?: Str::slug((string) $group->title);
            $slug = $base;
            $suffix = 2;

            while (
                $base !== '' && static::query()
                    ->where('slug', $slug)
                    ->when($group->getKey(), fn ($query, $key) => $query->whereKeyNot($key))
                    ->exists()
            ) {
                $slug = $base.'-'.$suffix++;
            }

            $group->slug = $slug;
        });
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }
}
