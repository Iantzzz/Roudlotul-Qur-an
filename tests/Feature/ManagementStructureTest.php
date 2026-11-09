<?php

namespace Tests\Feature;

use App\Models\ManagementMember;
use App\Models\ManagementPeriod;
use App\Models\ManagementUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagementStructureTest extends TestCase
{
    use RefreshDatabase;

    private const JABATAN = [
        ['jabatan' => 'Pengasuh', 'nama' => "Ibu Nyai Hj. Anisatus Sa'diyah", 'urutan' => 1],
        ['jabatan' => 'Pembina Penasehat', 'nama' => 'Penasehat Halaman', 'urutan' => 2],
        ['jabatan' => 'Ketua Pembina', 'nama' => 'Ketua Halaman', 'urutan' => 3],
        ['jabatan' => 'Sekretaris', 'nama' => 'Sekretaris Halaman', 'urutan' => 4],
        ['jabatan' => 'Bendahara', 'nama' => 'Bendahara Halaman', 'urutan' => 5],
        ['jabatan' => 'Koord. kedisiplinan', 'nama' => 'Koord Kedisiplinan Halaman', 'urutan' => 6],
        ['jabatan' => 'Anggota Kedisiplinan', 'nama' => 'Anggota Kedisiplinan Halaman', 'urutan' => 7],
        ['jabatan' => 'Tim Media IT', 'nama' => 'Media Halaman Satu', 'urutan' => 8],
        ['jabatan' => 'Tim Media IT', 'nama' => 'Media Halaman Dua', 'urutan' => 9],
        ['jabatan' => 'Sarpras', 'nama' => 'Sarpras Halaman', 'urutan' => 10],
        ['jabatan' => 'Koord. Kebersihan', 'nama' => 'Koord Kebersihan Halaman', 'urutan' => 11],
        ['jabatan' => 'Anggota Kebersihan', 'nama' => 'Anggota Kebersihan Halaman', 'urutan' => 12],
        ['jabatan' => 'Koord. Kesenian & Olahraga', 'nama' => 'Koord Kesenian Halaman', 'urutan' => 13],
        ['jabatan' => 'Anggota Kesenian & Olahraga', 'nama' => 'Anggota Kesenian Halaman', 'urutan' => 14],
    ];

    public function test_profil_renders_saved_tree_with_all_members_and_gold_chain_order(): void
    {
        $period = ManagementPeriod::create(['label' => '2026/2027', 'is_active' => true, 'urutan' => 1]);
        $period->members()->createMany(self::JABATAN);
        \App\Support\ManagementBackfill::run();

        $response = $this->get('/profil');

        $response->assertOk()
            ->assertSee('org-chart')
            ->assertSee('Periode 2026/2027')
        ;

        foreach (self::JABATAN as $member) {
            $response->assertSee(e($member['nama']), false);
            $response->assertSee(e($member['jabatan']), false);
        }

        // Rantai terkonfirmasi membuat urutan visual: Pengasuh → Penasehat → Ketua.
        $response->assertSeeInOrder(['Pengasuh', 'Pembina Penasehat', 'Ketua Pembina'], false);
        $response->assertSee('org-branch--depth-2', false);
        $response->assertSee('org-branch--depth-3', false);
    }

    public function test_members_without_unit_are_visible_in_anggota_lain(): void
    {
        $period = ManagementPeriod::create(['label' => '2026/2027', 'is_active' => true, 'urutan' => 1]);
        $period->members()->createMany(self::JABATAN);
        \App\Support\ManagementBackfill::run();

        ManagementUnit::query()->where('nama', 'Media IT')->delete();

        $this->get('/profil')
            ->assertOk()
            ->assertSee('Anggota Lain')
            ->assertSee('Media Halaman Satu', false)
            ->assertSee('Media Halaman Dua', false);
    }

    public function test_structure_changes_in_one_period_do_not_affect_other_period(): void
    {
        $lama = ManagementPeriod::create(['label' => 'Lama', 'urutan' => 2]);
        $lama->members()->createMany([
            ['jabatan' => 'Pengasuh', 'nama' => 'Pengasuh Lama', 'urutan' => 1],
            ['jabatan' => 'Sekretaris', 'nama' => 'Sekretaris Lama', 'urutan' => 2],
        ]);
        \App\Support\ManagementBackfill::run();

        $baru = ManagementPeriod::create(['label' => 'Baru', 'is_active' => true, 'urutan' => 3]);
        $baru->members()->createMany(self::JABATAN);
        \App\Support\ManagementBackfill::run();

        $lamaUnitsBefore = ManagementUnit::query()->where('management_period_id', $lama->getKey())->count();
        $lamaMembersSnapshot = ManagementMember::query()->where('management_period_id', $lama->getKey())
            ->get()->map(fn ($m) => $m->toArray())->all();

        // Mutasi struktur periode BARU.
        $baruUnit = ManagementUnit::query()->where('management_period_id', $baru->getKey())->where('nama', 'Kedisiplinan')->first();
        $baruUnit->delete();

        // Periode lama tidak tersentuh.
        $lamaMembersAfter = ManagementMember::query()->where('management_period_id', $lama->getKey())
            ->get()->map(fn ($m) => $m->toArray())->all();
        $this->assertSame($lamaMembersSnapshot, $lamaMembersAfter);
        $this->assertSame($lamaUnitsBefore, ManagementUnit::query()->where('management_period_id', $lama->getKey())->count());

        // Publik tetap hanya menampilkan periode aktif.
        $this->get('/profil')
            ->assertOk()
            ->assertSee('Periode Baru')
            ->assertDontSee('Periode Lama')
            ->assertDontSee('Pengasuh Lama');
    }

    public function test_period_without_units_still_displays_its_members(): void
    {
        $period = ManagementPeriod::create(['label' => '2025/2026', 'is_active' => true, 'urutan' => 1]);
        $period->members()->createMany([
            ['jabatan' => 'Pembina', 'nama' => 'Pembina Tanpa Unit', 'urutan' => 1],
            ['jabatan' => 'Ketua', 'nama' => 'Ketua Tanpa Unit', 'urutan' => 2],
        ]);

        $this->get('/profil')
            ->assertOk()
            ->assertSee('org-chart')
            ->assertSee('Struktur 2025/2026')
            ->assertSee('Pembina Tanpa Unit', false)
            ->assertSee('Ketua Tanpa Unit', false);
    }
}
