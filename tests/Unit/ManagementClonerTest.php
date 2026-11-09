<?php

namespace Tests\Unit;

use App\Models\ManagementMember;
use App\Models\ManagementPeriod;
use App\Models\ManagementUnit;
use App\Support\ManagementCloner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use RuntimeException;

class ManagementClonerTest extends TestCase
{
    use RefreshDatabase;

    private const JABATAN = [
        ['jabatan' => 'Pengasuh', 'nama' => 'Pengasuh Sumber', 'urutan' => 1],
        ['jabatan' => 'Pembina Penasehat', 'nama' => 'Penasehat Sumber', 'urutan' => 2],
        ['jabatan' => 'Ketua Pembina', 'nama' => 'Ketua Sumber', 'urutan' => 3],
        ['jabatan' => 'Sekretaris', 'nama' => 'Sekretaris Sumber', 'urutan' => 4],
        ['jabatan' => 'Bendahara', 'nama' => 'Bendahara Sumber', 'urutan' => 5],
        ['jabatan' => 'Koord. kedisiplinan', 'nama' => 'Koord Ked', 'urutan' => 6],
        ['jabatan' => 'Anggota Kedisiplinan', 'nama' => 'Anggota Ked', 'urutan' => 7],
        ['jabatan' => 'Tim Media IT', 'nama' => 'Media Satu', 'urutan' => 8],
        ['jabatan' => 'Tim Media IT', 'nama' => 'Media Dua', 'urutan' => 9],
        ['jabatan' => 'Sarpras', 'nama' => 'Sarpras Satu', 'urutan' => 10],
        ['jabatan' => 'Koord. Kebersihan', 'nama' => 'Koord Bersih', 'urutan' => 11],
        ['jabatan' => 'Anggota Kebersihan', 'nama' => 'Anggota Bersih', 'urutan' => 12],
        ['jabatan' => 'Koord. Kesenian & Olahraga', 'nama' => 'Koord Kesenian', 'urutan' => 13],
        ['jabatan' => 'Anggota Kesenian & Olahraga', 'nama' => 'Anggota Kesenian', 'urutan' => 14],
    ];

    private function makeSourcePeriod(): ManagementPeriod
    {
        $source = ManagementPeriod::create([
            'label' => '2026/2027',
            'is_active' => true,
            'urutan' => 1,
        ]);

        $source->members()->createMany(self::JABATAN);
        \App\Support\ManagementBackfill::run();

        return $source;
    }

    public function test_clone_copies_units_chain_and_order_but_zero_members(): void
    {
        $source = $this->makeSourcePeriod();
        $target = ManagementPeriod::create([
            'label' => '2027/2028',
            'urutan' => 2,
        ]);

        $count = ManagementCloner::run($source, $target);

        $this->assertSame(10, $count);

        $targetUnits = $target->units()->get();
        $this->assertSame(10, $targetUnits->count());
        $this->assertSame(0, $target->members()->count());

        $sourceUnitByName = $source->units()->get()->keyBy(fn (ManagementUnit $u) => $u->nama);
        $targetUnitByName = $targetUnits->keyBy(fn (ManagementUnit $u) => $u->nama);

        foreach ($sourceUnitByName as $nama => $unit) {
            $this->assertArrayHasKey($nama, $targetUnitByName);
            $this->assertSame((int) $unit->urutan, (int) $targetUnitByName[$nama]->urutan);
        }

        // Hubungan parent-child terpetakan utuh (sumber → target).
        $this->assertSame(
            (int) $targetUnitByName['Pengasuh']->getKey(),
            (int) $targetUnitByName['Pembina Penasehat']->parent_id,
        );
        $this->assertSame(
            (int) $targetUnitByName['Pembina Penasehat']->getKey(),
            (int) $targetUnitByName['Ketua Pembina']->parent_id,
        );
        $this->assertSame(
            (int) $targetUnitByName['Bidang Kepengurusan']->getKey(),
            (int) $targetUnitByName['Kedisiplinan']->parent_id,
        );

        // Sumber tidak disentuh.
        $this->assertSame(14, $source->members()->count());
        $this->assertSame(10, $source->units()->count());
    }

    public function test_clone_refuses_target_that_already_has_structure(): void
    {
        $source = $this->makeSourcePeriod();
        $target = ManagementPeriod::create([
            'label' => '2027/2028',
            'urutan' => 2,
        ]);

        ManagementCloner::run($source, $target);

        try {
            ManagementCloner::run($source, $target);

            $this->fail('Sekharusnya ditolak: target sudah punya struktur.');
        } catch (RuntimeException $e) {
            $this->assertSame(10, $target->units()->count());
            $this->assertSame(0, $target->members()->count());
        }
    }

    public function test_clone_refuses_same_period_and_empty_source(): void
    {
        $source = $this->makeSourcePeriod();

        try {
            ManagementCloner::run($source, $source);
            $this->fail('Sekharusnya ditolak: sumber = tujuan.');
        } catch (RuntimeException $e) {
        }

        $emptyTarget = ManagementPeriod::create(['label' => 'Kosong', 'urutan' => 3]);
        $emptySource = ManagementPeriod::create(['label' => 'Tanpa Struktur', 'urutan' => 4]);

        try {
            ManagementCloner::run($emptySource, $emptyTarget);
            $this->fail('Sekharusnya ditolak: sumber tanpa struktur.');
        } catch (RuntimeException $e) {
        }

        $this->assertNull($emptyTarget->units()->first());
    }
}
