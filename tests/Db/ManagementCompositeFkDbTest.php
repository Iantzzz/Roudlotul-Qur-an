<?php

namespace Tests\Db;

use App\Models\ManagementMember;
use App\Models\ManagementPeriod;
use App\Models\ManagementUnit;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Suite B — level DATABASE (bukan level model):
 * menguji jaminan yang ditegakkan oleh skema PostgreSQL itu sendiri
 * (composite FK ON DELETE SET NULL (kolom), cascade, dan trigger
 * penjaga satu-periode) dengan menulis LANGSUNG lewat DB dan
 * mem-bypass hook model/Eloquent.
 *
 * Target interlock: branch test rlq_test_* khusus testing (lihat
 * DbPgsqlTestCase). Semuanya jalannya pada database sintetis.
 */
class ManagementCompositeFkDbTest extends DbPgsqlTestCase
{
    public function test_composite_fk_rejects_unit_insert_with_parent_from_other_period(): void
    {
        $periodA = $this->makePeriod('Periode A');
        $periodB = $this->makePeriod('Periode B');

        $unitA = ManagementUnit::query()->create([
            'management_period_id' => (int) $periodA->getKey(),
            'parent_id' => null,
            'nama' => 'Unit A',
            'urutan' => 1,
        ]);

        $message = null;
        $state = null;

        try {
            // Menulis langsung (bypass model): parent dari PERIODE LAIN.
            DB::table('management_units')->insert([
                'management_period_id' => (int) $periodB->getKey(),
                'parent_id' => (int) $unitA->getKey(),
                'nama' => 'Unit B',
                'urutan' => 1,
            ]);
        } catch (Throwable $e) {
            $message = $e->getMessage();
            $state = self::sqlState($e);
        }

        $this->assertNotNull($message, 'Postingan lintas-periode wajib ditolak database.');
        $this->assertMatchesRegularExpression('/^(23503|P0001|23000)$/', (string) $state, 'SQLSTATE tak dikenal: '.$message);
    }

    public function test_composite_fk_rejects_member_insert_with_unit_from_other_period(): void
    {
        $periodA = $this->makePeriod('Periode A');
        $periodB = $this->makePeriod('Periode B');

        $unitA = ManagementUnit::query()->create([
            'management_period_id' => (int) $periodA->getKey(),
            'nama' => 'Unit A',
            'urutan' => 1,
        ]);

        $message = null;
        $state = null;

        try {
            DB::table('management_members')->insert([
                'management_period_id' => (int) $periodB->getKey(),
                'management_unit_id' => (int) $unitA->getKey(),
                'jabatan' => 'Sekretaris',
                'nama' => 'Sintetis B',
                'urutan' => 1,
            ]);
        } catch (Throwable $e) {
            $message = $e->getMessage();
            $state = self::sqlState($e);
        }

        $this->assertNotNull($message, 'Anggota lintas-periode wajib ditolak database.');
        $this->assertMatchesRegularExpression('/^(23503|P0001|23000)$/', (string) $state, 'SQLSTATE tak dikenal: '.$message);
    }

    public function test_deleting_unit_keeps_members_alive_and_keeps_period_intact(): void
    {
        $period = $this->makePeriod('Periode Uji', true);

        $unit = ManagementUnit::query()->create([
            'management_period_id' => (int) $period->getKey(),
            'nama' => 'Kedisiplinan',
            'urutan' => 1,
        ]);

        /** @var ManagementMember $member */
        $member = $period->members()->create([
            'management_unit_id' => (int) $unit->getKey(),
            'jabatan' => 'Koord. kedisiplinan',
            'nama' => 'Sintetis Koord',
            'urutan' => 1,
        ]);

        // Hapus unit secara DB-level (ini yang ditegakkan FK).
        DB::table('management_units')->whereKey($unit->getKey())->delete();

        $this->assertDatabaseHas('management_members', [
            'id' => $member->getKey(),
            'management_period_id' => (int) $period->getKey(),
            'jabatan' => 'Koord. kedisiplinan',
            'nama' => 'Sintetis Koord',
        ]);

        $row = DB::table('management_members')->whereKey($member->getKey())->first();

        $this->assertNull($row->management_unit_id, 'Kolom management_unit_id harus SET NULL, bukan ikut terhapus.');

        $this->assertDatabaseHas('management_periods', [
            'id' => $period->getKey(),
            'label' => 'Periode Uji',
        ]);
    }

    public function test_deleting_parent_unit_sets_children_parent_id_null(): void
    {
        $period = $this->makePeriod('Periode Uji');

        $root = ManagementUnit::query()->create([
            'management_period_id' => (int) $period->getKey(),
            'nama' => 'Pengasuh',
            'urutan' => 1,
        ]);

        $child = ManagementUnit::query()->create([
            'management_period_id' => (int) $period->getKey(),
            'parent_id' => (int) $root->getKey(),
            'nama' => 'Pembina Penasehat',
            'urutan' => 1,
        ]);

        $grandChild = ManagementUnit::query()->create([
            'management_period_id' => (int) $period->getKey(),
            'parent_id' => (int) $child->getKey(),
            'nama' => 'Ketua Pembina',
            'urutan' => 1,
        ]);

        DB::table('management_units')->whereKey($root->getKey())->delete();

        $childRow = DB::table('management_units')->whereKey($child->getKey())->first();
        $grandRow = DB::table('management_units')->whereKey($grandChild->getKey())->first();

        $this->assertNotNull($childRow, 'Unit anak harus selamat (SET NULL), tidak ikut terhapus.');
        $this->assertNull($childRow->parent_id, 'Anak langsung naik ke akar (parent_id NULL).');
        $this->assertSame($period->getKey(), (int) $childRow->management_period_id, 'Periode anak tetap utuh.');

        $this->assertNotNull($grandRow, 'Unit cucu harus selamat.');
        $this->assertSame($child->getKey(), $grandRow->parent_id);
    }

    public function test_deleting_period_cascades_units_and_members_only_in_that_period(): void
    {
        $periodA = $this->makePeriod('Periode A');
        $periodB = $this->makePeriod('Periode B');

        $unitA = ManagementUnit::query()->create([
            'management_period_id' => (int) $periodA->getKey(),
            'nama' => 'Unit A',
            'urutan' => 1,
        ]);

        ManagementUnit::query()->create([
            'management_period_id' => (int) $periodB->getKey(),
            'nama' => 'Unit B',
            'urutan' => 1,
        ]);

        $periodA->members()->create([
            'management_unit_id' => (int) $unitA->getKey(),
            'jabatan' => 'Sekretaris',
            'nama' => 'Sintetis A',
            'urutan' => 1,
        ]);

        $periodB->members()->create([
            'jabatan' => 'Bendahara',
            'nama' => 'Sintetis B',
            'urutan' => 1,
        ]);

        // Hapus periode A (Eloquent delete → DB delete).
        $periodA->delete();

        $this->assertDatabaseMissing('management_units', ['id' => $unitA->getKey()]);
        $this->assertDatabaseMissing('management_members', ['jabatan' => 'Sekretaris']);
        $this->assertDatabaseHas('management_periods', ['id' => $periodB->getKey()]);
        $this->assertDatabaseHas('management_units', ['nama' => 'Unit B']);
        $this->assertDatabaseHas('management_members', ['nama' => 'Sintetis B']);
    }

    public function test_composite_constraint_definition_requires_pg15_else_marks_skipped(): void
    {
        $version = \App\Support\ManagementStructure::postgresVersion();

        $this->assertNotNull($version, 'Suite B harus di pgsql; versi tak terbaca.');

        if ($version < \App\Support\ManagementStructure::PG_MIN_COMPOSITE_FK) {
            // PostgreSQL < 15 menggunakan jalur trigger + FK sederhana;
            // cek definisi composite FK eksplisit TIDAK BERLAKU di sana.
            $this->markTestSkipped("PostgreSQL {$version} memakai jalur trigger, bukan composite FK (dibutuhkan >= 150000).");
        }

        $def = DB::selectOne(
            "SELECT pg_get_constraintdef(c.oid, false) AS def
             FROM pg_catalog.pg_constraint c
             JOIN pg_catalog.pg_class r ON r.oid = c.conrelid
             WHERE c.conname = ? AND r.relname = ?",
            ['management_units_parent_period_foreign', 'management_units'],
        );

        $this->assertNotNull($def, 'Composite FK management_units_parent_period_foreign tidak ditemukan.');

        $raw = ' '.$def->def;

        $this->assertStringContainsString('FOREIGN KEY', $raw);
        $this->assertStringContainsString('PARENT_ID', strtoupper($raw));
        $this->assertStringContainsString('MANAGEMENT_PERIOD_ID', strtoupper($raw));
        $this->assertStringContainsString('REFERENCES management_units', $raw);
        $this->assertStringContainsString('SET NULL', strtoupper($raw));
    }
}
