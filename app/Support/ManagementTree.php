<?php

namespace App\Support;

use App\Models\ManagementPeriod;
use App\Models\ManagementUnit;

/**
 * Membangun pohon unit dari baris flat di database — murni presentational,
 * tanpa keyword classifier dan tanpa asumsi jumlah kelompok/tingkat.
 * Seluruh nama & struktur mengikuti isi tabel management_units.
 *
 * Bentuk node:
 * [
 *     'unit' => ?ManagementUnit,   // null pada wadah sintetis
 *     'nama' => string,
 *     'members' => Collection<ManagementMember>,
 *     'children' => array<node>,
 *     'depth' => int,
 *     'fallback' => bool,
 * ]
 *
 * Jaminan:
 * - anggota tanpa wadah tetap tampil pada wadah "Anggota Lain";
 * - periode belum berunit tetap menampilkan seluruh anggota pada
 *   wadah "Struktur {label}";
 * - data isapan/dangling (hanya mungkin lewat penulisan di luar aplikasi)
 *   dipindahkan ke wadah fallback — tidak pernah node dihilangkan senyap,
 *   dan tidak pernah terjadi loop render.
 */
final class ManagementTree
{
    public static function build(ManagementPeriod $period): array
    {
        $units = $period->units;
        $members = $period->members;

        if ($units->isEmpty() && $members->isNotEmpty()) {
            return [
                'roots' => [
                    self::syntheticNode('Struktur '.$period->label, $members, 1),
                ],
            ];
        }

        $byUnit = $members->groupBy(fn ($member) => $member->management_unit_id === null
            ? null
            : (string) $member->management_unit_id);

        /** @var array<string, array> $nodes */
        $nodes = [];

        foreach ($units as $unit) {
            $nodes[(string) $unit->getKey()] = [
                'unit' => $unit,
                'nama' => $unit->nama,
                'members' => $byUnit->get((string) $unit->getKey(), collect())->values(),
                'children' => [],
                'depth' => 0,
                'fallback' => false,
            ];
        }

        $roots = [];

        foreach ($nodes as $id => $node) {
            /** @var ManagementUnit $unit */
            $unit = $node['unit'];
            $parentId = $unit->parent_id;

            if ($parentId === null) {
                $roots[] = &$nodes[$id];

                continue;
            }

            $parentKey = (string) $parentId;

            if (isset($nodes[$parentKey])) {
                $nodes[$parentKey]['children'][] = &$nodes[$id];
            } else {
                // Parent hilang (penulisan di luar aplikasi): node tetap
                // tampil — ditaruh di akar sebagai wadah fallback.
                $dead = &$nodes[$id];
                $dead['fallback'] = true;
                $roots[] = &$dead;
            }
        }

        $visited = [];
        self::walk($roots, 1, $visited);

        // Sweep defensif: node yang tidak terjangkau dari akar (hanya
        // mungkin berupa siklus parent lewat penulisan luar aplikasi)
        // dipindahkan ke wadah fallback dengan struktur di-DASARKAN —
        // tanpa duplikasi render dan tanpa loop.
        $fallbackNodes = [];

        foreach ($nodes as $id => $node) {
            if (! isset($visited[$id])) {
                $unreachable = &$nodes[$id];
                $unreachable['children'] = [];
                $unreachable['fallback'] = true;
                $fallbackNodes[] = $unreachable;
            }
        }

        $orphans = $byUnit->get(null, collect())->values();

        $roots = array_merge($roots, $fallbackNodes);

        if ($orphans->isNotEmpty()) {
            $roots[] = self::syntheticNode('Anggota Lain', $orphans, 1);
        }

        return ['roots' => $roots];
    }

    /**
     * Rantai leluhur mulai dari unit (root = kedalaman 1): dipakai
     * validasi model untuk batas kedalaman & anti-siklus. Guard visited
     * membuat data isapan tetap menghentikan, tidak pernah loop.
     *
     * @return array{ids: list<int>, depth: int}
     */
    public static function ancestorChain(ManagementUnit $unit): array
    {
        $visited = [];
        $ids = [];
        $depth = 0;

        $current = $unit;

        while ($current->parent_id !== null) {
            $parentId = (string) $current->parent_id;

            if (isset($visited[$parentId])) {
                // Siklus pada data (penulisan di luar aplikasi): berhenti
                // aman dengan kedalaman maksimum agar penyimpanan ditolak.
                $depth = ManagementStructure::MAX_DEPTH;

                break;
            }

            $visited[$parentId] = true;
            $ids[] = (int) $current->parent_id;

            $parent = ManagementUnit::query()->find($parentId);

            if ($parent === null) {
                break;
            }

            $current = $parent;
            $depth++;
        }

        return ['ids' => $ids, 'depth' => $depth];
    }

    /**
     * Kedalaman paling dalam relatif unit ini (unit dikirim = 1).
     * Dipakai oleh ManagementUnit saat reparent agar seluruh turunan
     * melewati batas kedalaman ditolak saat simpan.
     */
    public static function subtreeDepth(ManagementUnit $unit): int
    {
        $deepest = 1;
        $visited = [];
        $stack = [[$unit, 1]];

        while ($stack !== []) {
            [$current, $depth] = array_pop($stack);
            $key = (string) $current->getKey();

            if (isset($visited[$key])) {
                continue;
            }

            $visited[$key] = true;
            $deepest = max($deepest, $depth);

            foreach (ManagementUnit::query()->where('parent_id', $current->getKey())->get() as $child) {
                $stack[] = [$child, $depth + 1];
            }
        }

        return $deepest;
    }

    /**
     * Jumlah seluruh turunan (semua tingkat) di bawah unit — untuk
     * modal dampak penghapusan unit di Filament.
     */
    public static function descendantCount(ManagementUnit $unit): int
    {
        $count = 0;
        $visited = [];
        $stack = [$unit->getKey()];

        while ($stack !== []) {
            $parentId = array_pop($stack);
            $key = (string) $parentId;

            if (isset($visited[$key])) {
                continue;
            }

            $visited[$key] = true;

            foreach (ManagementUnit::query()->where('parent_id', $parentId)->get() as $child) {
                $count++;
                $stack[] = $child->getKey();
            }
        }

        return $count;
    }

    /**
     * @param  array  $nodes
     * @param  array<string, true>  $visited
     */
    private static function walk(array &$nodes, int $depth, array &$visited): void
    {
        foreach ($nodes as &$node) {
            /** @var ?ManagementUnit $unit */
            $unit = $node['unit'];

            if ($unit === null) {
                $node['depth'] = $depth;

                continue;
            }

            $key = (string) $unit->getKey();

            if (isset($visited[$key])) {
                continue; // siklus: sweep tak-terjangkau menangkapnya
            }

            $visited[$key] = true;
            $node['depth'] = $depth;

            self::walk($node['children'], $depth + 1, $visited);
        }
    }

    private static function syntheticNode(string $nama, $members, int $depth): array
    {
        return [
            'unit' => null,
            'nama' => $nama,
            'members' => $members,
            'children' => [],
            'depth' => $depth,
            'fallback' => true,
        ];
    }
}
