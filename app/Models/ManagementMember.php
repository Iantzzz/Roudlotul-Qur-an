<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

/**
 * Salah satu anggota kepengurusan pada satu periode: jabatan diketik bebas
 * dan urutan tampil diatur manual sehingga perubahan susunan antarperiode
 * tetap sederhana.
 *
 * Tambahannya: anggota dapat wadah (unit) dari PERIODE YANG SAMA —
 * dijaga model + Filament, dan di PostgreSQL pula oleh composite FK.
 */
#[Fillable(['management_period_id', 'management_unit_id', 'jabatan', 'nama', 'urutan'])]
class ManagementMember extends Model
{
    protected static function booted(): void
    {
        static::saving(function (self $member): void {
            if ($member->management_unit_id === null) {
                return;
            }

            $unit = ManagementUnit::query()->find($member->management_unit_id);

            if ($unit === null) {
                throw ValidationException::withMessages([
                    'management_unit_id' => 'Wadah/unit tidak ditemukan.',
                ]);
            }

            if ((int) $unit->management_period_id !== (int) $member->management_period_id) {
                throw ValidationException::withMessages([
                    'management_unit_id' => 'Wadah harus berasal dari periode yang sama dengan anggota.',
                ]);
            }
        });
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(ManagementPeriod::class, 'management_period_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(ManagementUnit::class, 'management_unit_id');
    }
}
