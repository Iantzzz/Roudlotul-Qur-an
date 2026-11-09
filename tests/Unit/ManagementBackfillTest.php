<?php

namespace Tests\Unit;

use App\Models\ManagementMember;
use App\Models\ManagementPeriod;
use App\Models\ManagementUnit;
use App\Support\ManagementBackfill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagementBackfillTest extends TestCase
{
    use RefreshDatabase;

    /** Jabatan produksi saat ini (urutan tampil 'urutan' asli). */
    private const PRODUCTION_JABATAN = [
        ['jabatan' => 'Pengasuh', 'nama' => 'Ibu Nyai Hj. Anisatus Sa\'diyah', 'urutan' => 1],
        ['jabatan' => 'Pembina Penasehat', 'nama' => 'Penasehat Satu', 'urutan' => 2],
        ['jabatan' => 'Ketua Pembina', 'nama' => 'Ketua Satu', 'urutan' => 3],
        ['jabatan' => 'Sekretaris', 'nama' => 'Nama Sekretaris', 'urutan' => 4],
        ['jabatan' => 'Bendahara', 'nama' => 'Nama Bendahara', 'urutan' => 5],
        ['jabatan' => 'Koord. kedisiplinan', 'nama' => 'Koord Kedisiplinan', 'urutan' => 6],
        ['jabatan' => 'Anggota Kedisiplinan', 'nama' => 'Anggota Kedisiplinan', 'urutan' => 7],
        ['jabatan' => 'Tim Media IT', 'nama' => 'Tim Media IT Satu', 'urutan' => 8],
        ['jabatan' => 'Tim Media IT', 'nama' => 'Tim Media IT Dua', 'urutan' => 9],
        ['jabatan' => 'Sarpras', 'nama' => 'Nama Sarpras', 'urutan' => 10],
        ['jabatan' => 'Koord. Kebersihan', 'nama' => 'Koord Kebersihan', 'urutan' => 11],
        ['jabatan' => 'Anggota Kebersihan', 'nama' => 'Anggota Kebersihan', 'urutan' => 12],
        ['jabatan' => 'Koord. Kesenian & Olahraga', 'nama' => 'Koord Kesenian', 'urutan' => 13],
        ['jabatan' => 'Anggota Kesenian & Olahraga', 'nama' => 'Anggota Kesenian', 'urutan' => 14],
    ];

    public function test_backfill_maps_confirmed_hierarchy_without_touching_member_identity(): void
    {
        $period = ManagementPeriod::create([
            'label' => '2026/2027',
            'is_active' => true,
            'urutan' => 1,
        ]);

        $period->members()->createMany(self::PRODUCTION_JABATAN);
        $snapshot = collect($period->members()->get()->values())
            ->map(fn (ManagementMember $m): array => [
                'jabatan' => $m->jabatan,
                'nama' => $m->nama,
                'urutan' => (int) $m->urutan,
                'management_period_id' => (int) $m->management_period_id,
            ]);

        $summary = ManagementBackfill::run();

        $this->assertSame('mapped', $summary[0]['mode']);
        $this->assertSame(10, $summary[0]['units']);
        $this->assertSame(14, $summary[0]['members']);

        // Nama/jabatan/urutan/periode TIDAK berubah satu byte.
        $fresh = ManagementMember::query()->where('management_period_id', $period->getKey())->orderBy('urutan')->get()
            ->map(fn (ManagementMember $m): array => [
                'jabatan' => $m->jabatan,
                'nama' => $m->nama,
                'urutan' => (int) $m->urutan,
                'management_period_id' => (int) $m->management_period_id,
            ]);

        $this->assertSame($snapshot->all(), $fresh->all());

        // Seluruh anggota menempel pada unit periode yang sama.
        $period->refresh();
        $unitIds = $period->units()->pluck('id');
        $attached = $period->members()->pluck('management_unit_id');
        $this->assertTrue($attached->every(fn ($unitId) => $unitId !== null && $unitIds->contains($unitId)));

        $unitNames = $period->units()->pluck('nama');
        foreach (['Pengasuh', 'Pembina Penasehat', 'Ketua Pembina', 'Pengurus Inti', 'Bidang Kepengurusan', 'Kedisiplinan', 'Media IT', 'Sarana & Prasarana', 'Kebersihan', 'Kesenian & Olahraga'] as $expected) {
            $this->assertTrue($unitNames->contains($expected), "Unit {$expected} dibuat.");
        }

        $unitByName = $period->units()->get()->keyBy(fn (ManagementUnit $u) => $u->nama);

        // Rantai terkonfirmasi: Pengasuh → Pembina Penasehat → Ketua Pembina.
        $pengasuhId = $unitByName['Pengasuh']->getKey();
        $pembinaId = $unitByName['Pembina Penasehat']->getKey();
        $this->assertSame((int) $pengasuhId, (int) $unitByName['Pembina Penasehat']->parent_id);
        $this->assertSame((int) $pembinaId, (int) $unitByName['Ketua Pembina']->parent_id);

        // Pengurus Inti & bidang berdiri sebagai wadah root terpisah.
        $this->assertNull($unitByName['Pengurus Inti']->parent_id);
        $this->assertNull($unitByName['Bidang Kepengurusan']->parent_id);
        $this->assertSame((int) $unitByName['Bidang Kepengurusan']->getKey(), (int) $unitByName['Kedisiplinan']->parent_id);
    }

    public function test_backfill_is_idempotent_double_run_creates_no_duplicates(): void
    {
        $period = ManagementPeriod::create([
            'label' => '2026/2027',
            'is_active' => true,
            'urutan' => 1,
        ]);

        $period->members()->createMany(self::PRODUCTION_JABATAN);

        ManagementBackfill::run();
        $snapshot = ManagementMember::query()->where('management_period_id', $period->getKey())->get()
            ->map(fn ($m) => $m->toArray())->all();

        ManagementBackfill::run();

        $subjects = ManagementPeriod::query()->whereKey($period->getKey())->with('units')->first();

        $this->assertSame(10, $subjects->units()->count());

        $after = ManagementMember::query()->where('management_period_id', $period->getKey())->get()
            ->map(fn ($m) => $m->toArray())->all();

        $this->assertSame($snapshot, $after);
    }

    public function test_unrecognized_period_gets_single_container(): void
    {
        $old = ManagementPeriod::create([
            'label' => 'Riwayat 2020/2021',
            'urutan' => 3,
        ]);

        $old->members()->createMany([
            ['jabatan' => 'Majelis Tak Dikenal', 'nama' => 'Nama A', 'urutan' => 1],
            ['jabatan' => 'Peran Lain', 'nama' => 'Nama B', 'urutan' => 2],
        ]);

        $first = ManagementBackfill::run();
        $second = ManagementBackfill::run();

        $this->assertSame('single-container', $first[0]['mode']);
        $this->assertSame('skipped-already-structured', $second[0]['mode']);

        $units = ManagementUnit::query()->where('management_period_id', $old->getKey())->get();
        $this->assertSame(1, $units->count());
        $this->assertSame('Struktur Riwayat 2020/2021', $units->first()->nama);

        $attached = $old->members()->whereNotNull('management_unit_id')->count();
        $this->assertSame(2, $attached);
    }
}
