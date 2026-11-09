<?php

namespace App\Models;

use App\Support\ManagementStructure;
use App\Support\ManagementTree;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

/**
 * Unit kepengurusan (kelompok/bidang/jabatan) pada satu periode, tersusun
 * pohon melalui parent_id. Seluruh nama & struktur dibaca dari baris ini —
 * frontend merender apa yang tersimpan (tanpa classifier di kode).
 *
 * Aturan integritas (dijaga model + Filament; di PostgreSQL pula dijaga
 * composite FK/trigger tergantung versi):
 * - parent harus satu periode dengan unit,
 * - parent tidak boleh diri sendiri atau keturunan diri sendiri (anti-siklus),
 * - kedalaman pohon (root = 1) maksimum ManagementStructure::MAX_DEPTH —
 *   pelanggaran DITOLAK eksplisit saat simpan, bukan dihilangkan saat render.
 */
#[Fillable(['management_period_id', 'parent_id', 'nama', 'urutan'])]
class ManagementUnit extends Model
{
    protected static function booted(): void
    {
        static::saving(function (self $unit): void {
            self::validate($unit);
        });
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(ManagementPeriod::class, 'management_period_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('urutan')->orderBy('id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ManagementMember::class, 'management_unit_id')
            ->orderBy('urutan')
            ->orderBy('id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'management_period_id' => 'integer',
            'parent_id' => 'integer',
            'urutan' => 'integer',
        ];
    }

    private static function validate(self $unit): void
    {
        $errors = [];

        $periodId = (int) ($unit->management_period_id ?? 0);

        if ($periodId <= 0) {
            $errors['management_period_id'] = 'Unit wajib berada di dalam satu periode.';

            throw ValidationException::withMessages($errors);
        }

        $nama = trim((string) $unit->nama);

        if ($nama === '' || $nama === null) {
            $errors['nama'] = 'Nama unit wajib diisi.';
        }

        if (mb_strlen($nama) > 100) {
            $errors['nama'] = 'Nama unit maksimal 100 karakter.';
        }

        $parentId = $unit->parent_id;
        $selfKey = $unit->exists ? (int) $unit->getKey() : null;

        if ($parentId === null) {
            if ($errors !== []) {
                throw ValidationException::withMessages($errors);
            }

            return;
        }

        $parentId = (int) $parentId;

        $parent = self::query()->find($parentId);

        if ($parent === null) {
            $errors['parent_id'] = 'Unit induk tidak ditemukan.';
        } else {
            if ($selfKey !== null && $parentId === $selfKey) {
                $errors['parent_id'] = 'Unit tidak dapat menjadi induk bagi dirinya sendiri.';
            } else {
                $chain = ManagementTree::ancestorChain($parent);

                if ($selfKey !== null && in_array($selfKey, $chain['ids'], true)) {
                    $errors['parent_id'] = 'Unit induk tidak boleh berada di bawah unit ini (siklus tak diperbolehkan).';
                } else {
                    if ((int) $parent->management_period_id !== $periodId) {
                        $errors['parent_id'] = 'Unit induk harus berasal dari periode yang sama.';
                    } else {
                        // Kedalaman unit baru = (jumlah leluhur parent) + 1
                        // untuk parent + 1 untuk unit ini (root = tingkat 1).
                        $depth = $chain['depth'] + 2;

                        if ($depth > ManagementStructure::MAX_DEPTH) {
                            $errors['parent_id'] = sprintf(
                                'Kedalaman maksimum struktur adalah %d tingkat; unit ini akan berada di tingkat %d.',
                                ManagementStructure::MAX_DEPTH,
                                $depth,
                            );
                        } elseif ($selfKey !== null) {
                            // Reparent: pastikan seluruh turunan unit juga masih
                            // tidak melewati batas kedalaman setelah dipindah.
                            $subtreeDepth = ManagementTree::subtreeDepth($unit);

                            if ($depth + $subtreeDepth - 1 > ManagementStructure::MAX_DEPTH) {
                                $errors['parent_id'] = sprintf(
                                    'Memindahkan unit ini akan membuat salah satu turunannya berada di tingkat lebih dari %d.',
                                    ManagementStructure::MAX_DEPTH,
                                );
                            }
                        }
                    }
                }
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
