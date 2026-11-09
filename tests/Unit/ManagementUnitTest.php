<?php

namespace Tests\Unit;

use App\Models\ManagementMember;
use App\Models\ManagementPeriod;
use App\Models\ManagementUnit;
use App\Support\ManagementTree;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ManagementUnitTest extends TestCase
{
    use RefreshDatabase;

    private const JABATAN = [
        ['jabatan' => 'Pengasuh', 'nama' => 'Pengasuh Uji', 'urutan' => 1],
        ['jabatan' => 'Pembina Penasehat', 'nama' => 'Penasehat Uji', 'urutan' => 2],
        ['jabatan' => 'Ketua Pembina', 'nama' => 'Ketua Uji', 'urutan' => 3],
        ['jabatan' => 'Sekretaris', 'nama' => 'Sekretaris Uji', 'urutan' => 4],
        ['jabatan' => 'Bendahara', 'nama' => 'Bendahara Uji', 'urutan' => 5],
        ['jabatan' => 'Koord. kedisiplinan', 'nama' => 'Koord Ked', 'urutan' => 6],
        ['jabatan' => 'Anggota Kedisiplinan', 'nama' => 'Anggota Ked', 'urutan' => 7],
        ['jabatan' => 'Tim Media IT', 'nama' => 'Media Satu', 'urutan' => 8],
        ['jabatan' => 'Tim Media IT', 'nama' => 'Media Dua', 'urutan' => 9],
        ['jabatan' => 'Sarpras', 'nama' => 'Sarpras', 'urutan' => 10],
        ['jabatan' => 'Koord. Kebersihan', 'nama' => 'Koord Bersih', 'urutan' => 11],
        ['jabatan' => 'Anggota Kebersihan', 'nama' => 'Anggota Bersih', 'urutan' => 12],
        ['jabatan' => 'Koord. Kesenian & Olahraga', 'nama' => 'Koord Kesenian', 'urutan' => 13],
        ['jabatan' => 'Anggota Kesenian & Olahraga', 'nama' => 'Anggota Kesenian', 'urutan' => 14],
    ];

    public function test_deleting_unit_keeps_members_and_falls_back_to_other_members(): void
    {
        $period = ManagementPeriod::create(['label' => '2026/2027', 'is_active' => true, 'urutan' => 1]);
        $period->members()->createMany(self::JABATAN);

        // Jalankan backfill agar seluruh anggota menempel.
        \App\Support\ManagementBackfill::run();

        $bidang = ManagementUnit::query()->where('nama', 'Kedisiplinan')->firstOrFail();

        $before = ManagementMember::query()->count();

        $bidang->delete();

        $after = ManagementMember::query()->count();
        $this->assertSame($before, $after, 'Anggota tidak boleh ikut terhapus bersama unit.');

        $affected = ManagementMember::query()->whereIn('jabatan', ['Koord. kedisiplinan', 'Anggota Kedisiplinan'])
            ->get();

        $this->assertCount(2, $affected);

        foreach ($affected as $member) {
            $this->assertNull($member->management_unit_id);
            $this->assertSame((int) $period->getKey(), (int) $member->management_period_id);
        }

        $tree = ManagementTree::build($period);
        $fallback = collect($tree['roots'])->firstWhere('fallback', true);

        $this->assertNotNull($fallback, 'Wadah Anggota Lain tampil.');
        $this->assertSame('Anggota Lain', $fallback['nama']);
        $this->assertSame(2, $fallback['members']->count());
    }

    public function test_deleting_parent_unit_promotes_children_without_losing_period(): void
    {
        $period = ManagementPeriod::create(['label' => '2026/2027', 'is_active' => true, 'urutan' => 1]);
        $period->members()->createMany(self::JABATAN);
        \App\Support\ManagementBackfill::run();

        $root = ManagementUnit::query()->where('nama', 'Pengasuh')->firstOrFail();
        $pembina = ManagementUnit::query()->where('nama', 'Pembina Penasehat')->firstOrFail();
        $ketua = ManagementUnit::query()->where('nama', 'Ketua Pembina')->firstOrFail();

        $root->delete();

        $pembina->refresh();
        $ketua->refresh();

        $this->assertNull($pembina->parent_id, 'Anak langsung naik menjadi akar.');
        $this->assertSame($period->getKey(), $pembina->management_period_id);

        $this->assertSame((int) $pembina->getKey(), (int) $ketua->parent_id, 'Cucu tetap pada induknya.');
        $this->assertSame($period->getKey(), $ketua->management_period_id);

        $this->assertTrue(ManagementMember::query()->count() >= 14);
    }

    public function test_parent_and_member_walls_reject_cross_period(): void
    {
        $a = ManagementPeriod::create(['label' => 'Periode A', 'is_active' => true, 'urutan' => 1]);
        $b = ManagementPeriod::create(['label' => 'Periode B', 'urutan' => 2]);

        $unitA = ManagementUnit::create([
            'management_period_id' => $a->getKey(),
            'parent_id' => null,
            'nama' => 'Unit A',
            'urutan' => 1,
        ]);

        // Parent unit lintas periode ditolak (model).
        try {
            ManagementUnit::create([
                'management_period_id' => $b->getKey(),
                'parent_id' => (int) $unitA->getKey(),
                'nama' => 'Unit B',
                'urutan' => 1,
            ]);

            $this->fail('Parent lintas periode harus ditolak.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('parent_id', $e->errors());
        }

        // Anggota lintas-periode ditolak (model hook).
        try {
            ManagementMember::create([
                'management_period_id' => $b->getKey(),
                'management_unit_id' => (int) $unitA->getKey(),
                'jabatan' => 'Sekretaris',
                'nama' => 'Nama',
                'urutan' => 1,
            ]);

            $this->fail('Anggota dengan wadah lintas periode harus ditolak.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('management_unit_id', $e->errors());
        }
    }

    public function test_circular_hierarchy_is_prevented(): void
    {
        $period = ManagementPeriod::create(['label' => '2026/2027', 'is_active' => true, 'urutan' => 1]);

        $a = ManagementUnit::create(['management_period_id' => (int) $period->getKey(), 'nama' => 'A', 'urutan' => 1]);
        $b = ManagementUnit::create(['management_period_id' => (int) $period->getKey(), 'parent_id' => (int) $a->getKey(), 'nama' => 'B', 'urutan' => 2]);

        try {
            $a->forceFill(['parent_id' => (int) $b->getKey()])->save();

            $this->fail('Siklus harus ditolak.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('parent_id', $e->errors());
        }
    }

    public function test_depth_beyond_cap_is_rejected_at_save(): void
    {
        $period = ManagementPeriod::create(['label' => '2026/2027', 'is_active' => true, 'urutan' => 1]);

        $parent = null;
        $chainIds = [];

        for ($level = 1; $level <= 6; $level++) {
            $unit = ManagementUnit::create([
                'management_period_id' => (int) $period->getKey(),
                'parent_id' => $parent !== null ? (int) $parent->getKey() : null,
                'nama' => 'Level '.$level,
                'urutan' => 1,
            ]);

            $parent = $unit;
            $chainIds[] = (int) $unit->getKey();
        }

        // Level ke-7 = melebihi MAX_DEPTH (6) → ditolak eksplisit.
        try {
            ManagementUnit::create([
                'management_period_id' => (int) $period->getKey(),
                'parent_id' => (int) $parent->getKey(),
                'nama' => 'Level 7',
                'urutan' => 1,
            ]);

            $this->fail('Kedalaman 7 harus ditolak.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('parent_id', $e->errors());
        }
    }

    public function test_reparenting_subtree_that_would_exceed_depth_is_rejected(): void
    {
        $period = ManagementPeriod::create(['label' => '2026/2027', 'is_active' => true, 'urutan' => 1]);

        // Rantai dalam: 1..5.
        $parent = null;

        for ($level = 1; $level <= 5; $level++) {
            $parent = ManagementUnit::create([
                'management_period_id' => (int) $period->getKey(),
                'parent_id' => $parent !== null ? (int) $parent->getKey() : null,
                'nama' => 'Rantai '.$level,
                'urutan' => 1,
            ]);
        }

        // Pohon lepas dua tingkat: W3 → W4 (subtree depth = 2).
        $w3 = ManagementUnit::create(['management_period_id' => (int) $period->getKey(), 'nama' => 'W3', 'urutan' => 2]);
        ManagementUnit::create(['management_period_id' => (int) $period->getKey(), 'parent_id' => (int) $w3->getKey(), 'nama' => 'W4', 'urutan' => 1]);

        // Memindah W3 di bawah tingkat 5: W3→6, W4→7 → TOLAK.
        try {
            $w3->forceFill(['parent_id' => (int) $parent->getKey()])->save();

            $this->fail('Reparent yang membuat turunan melampaui kedalaman harus ditolak.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('parent_id', $e->errors());
        }
    }
}
