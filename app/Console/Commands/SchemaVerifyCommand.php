<?php

namespace App\Console\Commands;

use App\Support\ManagementStructure;
use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pemeriksaan kesiapan skema — read-only, dipanggil pada fase BUILD
 * Vercel (composer vercel) dan di langkah deployment terkondisi.
 *
 * Pemeriksaan meliputi:
 * 1. File migrasi pending (file di database/migrations belum tercatat di
 *    tabel migrations) → GAGAL.
 * 2. Manifest tabel, kolom, index/unique, dan constraint penting
 *    (driver-aware: pg_catalog ketat di PostgreSQL; PRAGMA di SQLite)
 *    → GAGAL bila ada satu saja hilang.
 *
 * Fail-closed: DB tak terjangkau atau manifest tak lengkap → exit 1,
 * sehingga Build GAGAL dan deployment lama tetap melayani.
 */
class SchemaVerifyCommand extends Command
{
    protected $signature = 'schema:verify {--json : Output JSON}';

    protected $description = 'Verifikasi read-only kesiapan skema: migrasi pending + manifest tabel/kolom/index/constraint.';

    /**
     * @var list<array{name: string, ok: bool, note: ?string}>
     */
    private array $checks = [];

    public function handle(): int
    {
        try {
            DB::select('SELECT 1');
        } catch (\Throwable $e) {
            $this->error('Koneksi database tidak dapat dibuat: '.mb_substr((string) $e->getMessage(), 0, 200));

            return self::FAILURE;
        }

        $driver = (string) DB::connection()->getDriverName();
        $pgVersion = ManagementStructure::postgresVersion();
        $composite = ManagementStructure::supportsCompositeSetNull($pgVersion);

        $this->checkPendingMigrations();
        $this->checkTables($driver);
        $this->checkColumns($driver);
        $this->checkIndexes($driver);
        $this->checkConstraints($driver, $composite);

        if ((bool) $this->option('json')) {
            $this->line(json_encode([
                'driver' => $driver,
                'postgres_version' => $pgVersion,
                'composite_fk_path' => $composite,
                'ok' => collect($this->checks)->every(fn (array $check): bool => $check['ok']),
                'checks' => $this->checks,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return collect($this->checks)->every(fn (array $check): bool => $check['ok'])
                ? self::SUCCESS
                : self::FAILURE;
        }

        foreach ($this->checks as $check) {
            $line = sprintf('%s %s', $check['ok'] ? '<fg=green>✓</>' : '<fg=red>✗</>', $check['name']);

            if ($check['note'] !== null) {
                $line .= ' <fg=gray>— '.$check['note'].'</>';
            }

            $this->line($line);
        }

        $allOk = collect($this->checks)->every(fn (array $check): bool => $check['ok']);

        if (! $allOk) {
            $this->newLine();
            $this->error('Skema BELUM siap: jalankan migration melalui langkah deployment terkondisi (DIRECT), kemudian ulangi schema:verify.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Skema siap (migrasi '.collect($this->checks)->where('name', 'Migrasi pending')->first()['note'].')');

        return self::SUCCESS;
    }

    private function add(string $name, bool $ok, ?string $note = null): void
    {
        $this->checks[] = ['name' => $name, 'ok' => $ok, 'note' => $note];
    }

    private function checkPendingMigrations(): void
    {
        $files = collect(glob(database_path('migrations').'/*.php'))
            ->map(fn (string $path): string => pathinfo($path, PATHINFO_FILENAME))
            ->sort()
            ->values();

        $applied = Schema::hasTable('migrations')
            ? collect(DB::table('migrations')->pluck('migration'))
            : collect([]);

        $pending = $files->diff($applied)->values();

        $this->add(
            'Migrasi pending',
            $pending->isEmpty(),
            $this->shortOf($files->count(), $applied->count(), $pending->count()),
        );
    }

    private function shortOf(int $fileCount, int $appliedCount, int $pendingCount): string
    {
        return sprintf('%d file, %d terpasang, %d pending', $fileCount, $appliedCount, $pendingCount);
    }

    private function checkTables(string $driver): void
    {
        $tables = ['users', 'management_periods', 'management_members'];

        if ($driver === 'sqlite' || $driver === 'pgsql') {
            $tables[] = 'management_units';
        }

        foreach ($tables as $table) {
            $exists = $driver === 'pgsql'
                ? DB::selectOne("SELECT to_regclass('public.{$table}') AS reg")->reg !== null
                : Schema::hasTable($table);

            $this->add("Tabel {$table}", (bool) $exists);
        }
    }

    private function checkColumns(string $driver): void
    {
        $requirements = [
            'management_periods' => ['id', 'label', 'is_active', 'urutan'],
            'management_members' => ['id', 'management_period_id', 'jabatan', 'nama', 'urutan'],
            'management_units' => ['id', 'management_period_id', 'parent_id', 'nama', 'urutan'],
        ];

        foreach ($requirements as $table => $columns) {
            $existing = Schema::hasTable($table)
                ? collect(Schema::getColumnListing($table))
                : collect([]);

            foreach ($columns as $column) {
                $this->add("Kolom {$table}.{$column}", $existing->contains($column));
            }
        }
    }

    private function checkIndexes(string $driver): void
    {
        /** @var Connection $conn */
        $conn = DB::connection();

        if ($driver === 'pgsql') {
            $required = ['management_units_period_unique', 'management_units_parent_id_index', 'management_units_period_id_index', 'management_members_unit_id_index'];

            $existing = collect($conn->select(
                'SELECT indexname FROM pg_indexes WHERE schemaname = current_schema()',
            ))->pluck('indexname');

            foreach ($required as $index) {
                $this->add("Index {$index}", $existing->contains($index));
            }

            return;
        }

        if ($driver === 'sqlite') {
            $existing = collect();

            try {
                $existing = collect($conn->select(
                    "SELECT name FROM sqlite_master WHERE type IN ('index','unique_index')",
                ))->pluck('name');
            } catch (\Throwable) {
            }

            foreach (
                [
                    'management_units_period_unique',
                    'management_units_parent_id_index',
                    'management_units_period_id_index',
                    'management_members_unit_id_index',
                ] as $index
            ) {
                $this->add("Index {$index}", $existing->contains($index));
            }
        }
    }

    private function checkConstraints(string $driver, bool $composite): void
    {
        /** @var Connection $conn */
        $conn = DB::connection();

        if ($driver === 'pgsql') {
            $cascadeTargets = [
                ['management_members', 'management_members_management_period_id_foreign'],
                ['management_units', 'management_units_management_period_id_foreign'],
            ];

            foreach ($cascadeTargets as [$table, $constraint]) {
                $def = $this->constraintDefinition($conn, $table, $constraint);

                $this->add(
                    "FK {$constraint}",
                    $def !== null && str_contains($this->upper($def), 'DELETE CASCADE'),
                    $def !== null ? mb_substr($def, 0, 90) : null,
                );
            }

            if ($composite) {
                $pair = [
                    ['management_units', 'management_units_parent_period_foreign', 'ON DELETE SET NULL (PARENT_ID)', ['PARENT_ID', 'MANAGEMENT_PERIOD_ID']],
                    ['management_members', 'management_members_unit_period_foreign', 'ON DELETE SET NULL (MANAGEMENT_UNIT_ID)', ['MANAGEMENT_UNIT_ID', 'MANAGEMENT_PERIOD_ID']],
                ];

                foreach ($pair as [$table, $constraint, $mustContain, $fkCols]) {
                    $def = $this->constraintDefinition($conn, $table, $constraint);
                    $upper = $this->upper((string) $def);

                    $fkOk = str_contains($upper, 'FOREIGN KEY (') !== false
                        && collect($fkCols)->every(fn (string $col): bool => str_contains($upper, $col));

                    $this->add(
                        "Composite FK {$constraint}",
                        $def !== null && str_contains($upper, $mustContain) && $fkOk,
                        $def !== null ? mb_substr($def, 0, 120) : null,
                    );
                }
            } else {
                $pair = [
                    ['management_units', 'management_units_parent_id_foreign', 'SET NULL'],
                    ['management_members', 'management_members_management_unit_id_foreign', 'SET NULL'],
                ];

                foreach ($pair as [$table, $constraint, $mustContain]) {
                    $def = $this->constraintDefinition($conn, $table, $constraint);

                    $this->add(
                        "FK {$constraint} (jalur trigger)",
                        $def !== null && str_contains($this->upper((string) $def), $mustContain),
                        $def !== null ? mb_substr($def, 0, 90) : null,
                    );
                }

                foreach (
                    [
                        'trg_management_units_parent_period',
                        'trg_management_members_unit_period',
                    ] as $trigger
                ) {
                    $exists = $conn->selectOne(
                        'SELECT COUNT(*) AS found FROM pg_catalog.pg_trigger t JOIN pg_catalog.pg_class r ON r.oid = t.tgrelid WHERE NOT t.tgisinternal AND r.relname = ? AND t.tgname = ?',
                        [$trigger === 'trg_management_units_parent_period' ? 'management_units' : 'management_members', $trigger],
                    );

                    $this->add("Trigger {$trigger}", (int) $exists->found > 0);
                }
            }

            return;
        }

        if ($driver === 'sqlite') {
            $fkRows = collect();

            try {
                $fkRows = collect($conn->select('PRAGMA foreign_key_list(management_units)'));
            } catch (\Throwable) {
            }

            $parentFk = $fkRows->first(fn ($row): bool => ($row->from ?? null) === 'parent_id'
                && ($row->table ?? null) === 'management_units'
                && ($row->on_delete ?? null) === 'SET NULL');

            $this->add('FK sqlite management_units.parent_id → SET NULL', $parentFk !== null);

            $memberFkRows = collect();

            try {
                $memberFkRows = collect($conn->select('PRAGMA foreign_key_list(management_members)'));
            } catch (\Throwable) {
            }

            $memberFk = $memberFkRows->first(fn ($row): bool => ($row->from ?? null) === 'management_unit_id'
                && ($row->table ?? null) === 'management_units'
                && ($row->on_delete ?? null) === 'SET NULL');

            $this->add('FK sqlite management_members.management_unit_id → SET NULL', $memberFk !== null);
        }
    }

    private function constraintDefinition(Connection $conn, string $table, string $constraint): ?string
    {
        try {
            $row = $conn->selectOne(
                'SELECT pg_get_constraintdef(c.oid, false) AS def
                 FROM pg_catalog.pg_constraint c
                 JOIN pg_catalog.pg_class r ON r.oid = c.conrelid
                 WHERE c.conname = ? AND r.relname = ? LIMIT 1',
                [$constraint, $table],
            );

            return $row?->def;
        } catch (\Throwable) {
            return null;
        }
    }

    private function upper(string $value): string
    {
        return mb_strtoupper(preg_replace('/\s+/', ' ', $value) ?? $value);
    }
}
