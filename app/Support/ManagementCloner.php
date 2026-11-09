<?php

namespace App\Support;

use App\Models\ManagementPeriod;
use App\Models\ManagementUnit;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Salin struktur pohon unit dari satu periode ke periode lain:
 * - menyalin unit, nama, hubungan parent-child, dan urutan —
 *   TIDAK menyalin data anggota sama sekali;
 * - periode sumber tidak disentuh;
 * - seluruh penulisan dalam satu transaksi (gagal = tidak ada sisa);
 * - tolak bila periode tujuan sudah punya struktur (cek ganda:
 *   di luar DAN di dalam transaksi untuk anti-race).
 */
final class ManagementCloner
{
    /**
     * @return int jumlah unit yang disalin
     */
    public static function run(ManagementPeriod $source, ManagementPeriod $target): int
    {
        if ((int) $source->getKey() === (int) $target->getKey()) {
            throw new RuntimeException('Periode sumber dan tujuan tidak boleh sama.');
        }

        return DB::transaction(function () use ($source, $target): int {
            $sourceUnits = $source->units()
                ->orderBy('urutan')
                ->orderBy('id')
                ->get();

            if ($sourceUnits->isEmpty()) {
                throw new RuntimeException('Periode sumber belum memiliki struktur unit untuk disalin.');
            }

            // Cek ulang di dalam transaksi (anti-race).
            if ($target->units()->exists()) {
                throw new RuntimeException('Periode tujuan sudah memiliki struktur unit. Salin dibatalkan.');
            }

            $depths = self::depthsByUnitId($sourceUnits);

            $map = [];
            $count = 0;

            // Salin per tingkat (BFS): parent selalu tercipta sebelum anak,
            // sehingga hubungan parent-child terpetakan utuh.
            $maxDepth = max($depths);

            for ($depth = 1; $depth <= $maxDepth; $depth++) {
                $level = $sourceUnits
                    ->filter(fn (ManagementUnit $unit) => ($depths[(string) $unit->getKey()] ?? 1) === $depth)
                    ->sortBy([['urutan', 'asc'], ['id', 'asc']])
                    ->values();

                /** @var ManagementUnit $unit */
                foreach ($level as $unit) {
                    /** @var ManagementUnit $new */
                    $new = $target->units()->create([
                        'parent_id' => $unit->parent_id !== null
                            ? ($map[(string) $unit->parent_id] ?? throw new RuntimeException('Peta unit induk tidak ditemukan saat menyalin.'))
                            : null,
                        'nama' => $unit->nama,
                        'urutan' => (int) $unit->urutan,
                    ]);

                    $map[(string) $unit->getKey()] = (int) $new->getKey();
                    $count++;
                }
            }

            return $count;
        });
    }

    /**
     * Kedalaman tiap unit pada struktur sumber (root = 1).
     *
     * @param  iterable<ManagementUnit>  $units
     * @return array<string, int>
     */
    private static function depthsByUnitId(iterable $units): array
    {
        $byId = [];

        foreach ($units as $unit) {
            $byId[(string) $unit->getKey()] = $unit;
        }

        return collect($units)
            ->mapWithKeys(function (ManagementUnit $unit) use ($byId): array {
                $depth = 1;
                $visited = [];
                $current = $unit;

                while ($current->parent_id !== null) {
                    $parentId = (string) $current->parent_id;

                    if (isset($visited[$parentId]) || ! isset($byId[$parentId])) {
                        break;
                    }

                    $visited[$parentId] = true;
                    $depth++;
                    $current = $byId[$parentId];
                }

                return [(string) $unit->getKey() => $depth];
            })
            ->all();
    }
}
