<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Foto hasil unggahan panel admin. Biner disimpan base64 karena filesystem
 * Vercel ephemeral — penyimpanan database membuat unggahan tahan deploymen.
 * Kolom data disembunyikan agar tidak ikut terserialisasi ke keluaran.
 */
#[Fillable(['name', 'mime', 'data', 'size'])]
class SiteMedia extends Model
{
    protected $hidden = ['data'];
}
