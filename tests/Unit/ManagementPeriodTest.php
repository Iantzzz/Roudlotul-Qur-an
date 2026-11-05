<?php

namespace Tests\Unit;

use App\Models\ManagementPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagementPeriodTest extends TestCase
{
    use RefreshDatabase;

    public function test_marking_a_period_active_deactivates_other_periods(): void
    {
        $lama = ManagementPeriod::create([
            'label' => 'Lama',
            'is_active' => true,
            'urutan' => 1,
        ]);

        $baru = ManagementPeriod::create([
            'label' => 'Baru',
            'is_active' => true,
            'urutan' => 2,
        ]);

        $lama->refresh();
        $baru->refresh();

        $this->assertFalse($lama->is_active);
        $this->assertTrue($baru->is_active);
    }

    public function test_public_active_returns_only_the_flagged_period_with_members(): void
    {
        $arsip = ManagementPeriod::create([
            'label' => 'Riwayat 2024/2025',
            'is_active' => false,
            'urutan' => 2,
        ]);

        $arsip->members()->create(['jabatan' => 'Ketua', 'nama' => 'Nama Riwayat', 'urutan' => 1]);

        $aktif = ManagementPeriod::create([
            'label' => 'Aktif 2025/2026',
            'is_active' => true,
            'urutan' => 1,
        ]);

        $aktif->members()->createMany([
            ['jabatan' => 'Pembina', 'nama' => 'Nama Anggota Satu', 'urutan' => 1],
            ['jabatan' => 'Ketua', 'nama' => 'Nama Anggota Dua', 'urutan' => 2],
        ]);

        $publik = ManagementPeriod::publicActive();

        $this->assertNotNull($publik);
        $this->assertSame('Aktif 2025/2026', $publik->label);
        $this->assertSame('Pembina', $publik->members->first()->jabatan);
        $this->assertCount(2, $publik->members);
    }

    public function test_public_active_returns_null_when_nothing_is_flagged(): void
    {
        $this->assertNull(ManagementPeriod::publicActive());
    }
}
