<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Periode kepengurusan asrama (struktur berganti tiap tahun). Persis satu
 * periode bertanda aktif — hanya periode itu yang tampil di halaman publik;
 * periode lain tersimpan sebagai riwayat dan hanya terlihat di panel admin.
 */
#[Fillable(['label', 'is_active', 'urutan'])]
class ManagementPeriod extends Model
{
    protected static function booted(): void
    {
        // Menandai satu periode aktif otomatis menonaktifkan periode lain
        // agar publik selalu memuat persis satu struktur yang berlaku.
        static::saved(function (self $period): void {
            if (! $period->is_active) {
                return;
            }

            static::withoutEvents(fn () => static::query()
                ->whereKeyNot($period->getKey())
                ->where('is_active', true)
                ->update(['is_active' => false]));
        });
    }

    public function members(): HasMany
    {
        return $this->hasMany(ManagementMember::class)->orderBy('urutan')->orderBy('id');
    }

    /**
     * Periode yang tampil di publik, siap dengan daftar anggotanya,
     * atau null bila tidak ada yang ditandai aktif.
     */
    public static function publicActive(): ?self
    {
        return static::query()
            ->where('is_active', true)
            ->with('members')
            ->orderBy('urutan')
            ->orderBy('id')
            ->first();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
