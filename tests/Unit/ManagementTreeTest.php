<?php

namespace Tests\Unit;

use App\Models\ManagementMember;
use App\Models\ManagementPeriod;
use App\Models\ManagementUnit;
use App\Support\ManagementTree;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ManagementTreeTest extends TestCase
{
    use RefreshDatabase;

    private function makePeriod(string $label = '2025/2026', bool $active = true): ManagementPeriod
    {
        return ManagementPeriod::create([
            'label' => $label,
            'is_active' => $active,
            'urutan' => 1,
        ]);
    }

    private function unit(ManagementPeriod $period, ?int $parentId, string $nama, int $urutan): ManagementUnit
    {
        return ManagementUnit::create([
            'management_period_id' => (int) $period->getKey(),
            'parent_id' => $parentId,
            'nama' => $nama,
            'urutan' => $urutan,
        ]);
    }

    private function member(ManagementPeriod $period, ?int $unitId, string $jabatan, string $nama, int $urutan): ManagementMember
    {
        return ManagementMember::create([
            'management_period_id' => (int) $period->getKey(),
            'management_unit_id' => $unitId,
            'jabatan' => $jabatan,
            'nama' => $nama,
            'urutan' => $urutan,
        ]);
    }

    public function test_build_preserves_saved_parent_chain_and_attaches_members(): void
    {
        $period = $this->makePeriod();

        $pengasuh = $this->unit($period, null, 'Pengasuh', 1);
        $pembina = $this->unit($period, (int) $pengasuh->getKey(), 'Pembina Penasehat', 1);
        $ketua = $this->unit($period, (int) $pembina->getKey(), 'Ketua Pembina', 1);

        $this->member($period, (int) $pengasuh->getKey(), 'Pengasuh', 'Ibu Nyai Hj. Anisatus Sa\'diyah', 1);
        $this->member($period, (int) $pembina->getKey(), 'Pembina Penasehat', 'Penasehat Satu', 2);

        $tree = ManagementTree::build($period);

        $this->assertCount(1, $tree['roots']);
        $root = $tree['roots'][0];
        $this->assertSame('Pengasuh', $root['nama']);
        $this->assertSame(1, $root['depth']);
        $this->assertSame(1, $root['members']->count());

        $this->assertCount(1, $root['children']);
        $child = $root['children'][0];
        $this->assertSame('Pembina Penasehat', $child['nama']);
        $this->assertSame(2, $child['depth']);
        $this->assertCount(1, $child['children']);
        $this->assertSame('Ketua Pembina', $child['children'][0]['nama']);
        $this->assertSame(3, $child['children'][0]['depth']);
    }

    public function test_members_without_unit_are_grouped_into_fallback_container(): void
    {
        $period = $this->makePeriod();

        $container = $this->unit($period, null, 'Pengurus Inti', 1);
        $this->member($period, (int) $container->getKey(), 'Sekretaris', 'Nama Satu', 1);
        // Tanpa wadah — mis. habis penghapusan unit.
        $this->member($period, null, 'Ketua', 'Nama Tanpa Wadah', 2);

        $tree = ManagementTree::build($period);

        $this->assertCount(2, $tree['roots']);
        $fallback = $tree['roots'][1];
        $this->assertTrue($fallback['fallback']);
        $this->assertSame('Anggota Lain', $fallback['nama']);
        $this->assertSame(1, $fallback['members']->count());
        $this->assertSame('Nama Tanpa Wadah', $fallback['members']->first()->nama);
    }

    public function test_period_without_units_still_shows_every_member(): void
    {
        $period = $this->makePeriod('Riwayat 2024/2025');

        $this->member($period, null, 'Pembina', 'Nama Satu', 1);
        $this->member($period, null, 'Ketua', 'Nama Dua', 2);

        $tree = ManagementTree::build($period);

        $this->assertCount(1, $tree['roots']);
        $node = $tree['roots'][0];
        $this->assertTrue($node['fallback']);
        $this->assertSame('Struktur Riwayat 2024/2025', $node['nama']);
        $this->assertSame(2, $node['members']->count());
    }

    public function test_dangling_parent_goes_to_fallback_roots(): void
    {
        $period = $this->makePeriod();
        $otherPeriod = $this->makePeriod('Periode Lain');

        // Unit pada periode lain — FK kolom tunggal memperkenankannya,
        // namun bagi pohon periode aktif id ini TIDAK ditemukan di antara
        // unit periodenya sendiri.
        $foreignParent = $this->unit($otherPeriod, null, 'Unit Periode Lain', 1);
        $child = $this->unit($period, null, 'Bidang Anak', 1);

        // Mensimulasikan penulisan di luar aplikasi (bypass model hook).
        DB::table('management_units')
            ->where('id', $child->getKey())
            ->update(['parent_id' => (int) $foreignParent->getKey()]);

        $tree = ManagementTree::build($period);

        $this->assertCount(1, $tree['roots']);
        $this->assertTrue($tree['roots'][0]['fallback']);
        $this->assertSame('Bidang Anak', $tree['roots'][0]['nama']);
    }

    public function test_cyclic_data_is_flattened_without_infinite_loop(): void
    {
        $period = $this->makePeriod();

        $a = $this->unit($period, null, 'Unit A', 1);
        $b = $this->unit($period, (int) $a->getKey(), 'Unit B', 1);

        DB::unprepared('PRAGMA foreign_keys = OFF');
        DB::table('management_units')->where('id', $a->getKey())->update(['parent_id' => $b->getKey()]);
        DB::unprepared('PRAGMA foreign_keys = ON');

        $tree = ManagementTree::build($period);

        $names = collect($tree['roots'])->pluck('nama')->all();
        $this->assertContains('Unit A', $names);
        $this->assertContains('Unit B', $names);
        foreach ($tree['roots'] as $root) {
            $this->assertTrue($root['fallback']);
            $this->assertSame([], $root['children']);
        }
    }
}
