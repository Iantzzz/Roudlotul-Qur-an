<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Salah satu anggota kepengurusan pada satu periode: jabatan diketik bebas
 * dan urutan tampil diatur manual sehingga perubahan susunan antarperiode
 * tetap sederhana.
 */
#[Fillable(['management_period_id', 'jabatan', 'nama', 'urutan'])]
class ManagementMember extends Model
{
    public function period(): BelongsTo
    {
        return $this->belongsTo(ManagementPeriod::class, 'management_period_id');
    }
}
