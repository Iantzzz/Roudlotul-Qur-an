<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ManagementSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_management_audit_reports_no_periods_and_hides_names(): void
    {
        $code = Artisan::call('management:audit');
        $output = Artisan::output();

        $this->assertSame(0, $code);
        $this->assertStringContainsString('AUDIT STRUKTUR KEPENGURUSAN', $output);
        $this->assertStringContainsString('Tidak ada periode', $output);
    }

    public function test_schema_verify_passes_after_migrations(): void
    {
        $code = Artisan::call('schema:verify');
        $output = Artisan::output();

        $this->assertSame(0, $code);
        $this->assertStringContainsString('Skema siap', $output);
        $this->assertStringContainsString('0 pending', $output);
    }

    public function test_management_audit_json_hides_member_names(): void
    {
        \App\Models\ManagementPeriod::create(['label' => '2026/2027', 'is_active' => true, 'urutan' => 1]);
        \App\Models\ManagementMember::create([
            'management_period_id' => 1,
            'jabatan' => 'Pengasuh',
            'nama' => 'Nama Rahasia',
            'urutan' => 1,
        ]);

        $code = Artisan::call('management:audit', ['--json' => true]);
        $output = Artisan::output();

        $this->assertSame(0, $code);
        $this->assertStringContainsString('"jabatan"', $output);
        // Audit TIDAK PERNAH menampilkan nama anggota.
        $this->assertStringNotContainsString('Nama Rahasia', $output);
        $this->assertStringContainsString('"is_active": true', $output);
        $this->assertStringContainsString('Pengasuh', $output);
    }
}
