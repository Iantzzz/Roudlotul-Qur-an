<?php

/**
 * SEMENTARA: endpoint diagnostik database untuk runtime Vercel.
 * Melaporkan fakta NON-rahasia: daftar tabel, boolean env (tanpa nilai),
 * output migrasi bila tabel kepengurusan belum ada. Hapus setelah pulih.
 */

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

$report = ['php' => PHP_VERSION];

try {
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    $appNameTables = static function (object $row): string {
        return (string) $row->table_name;
    };

    $listTables = static function (): array {
        return array_map(
            static fn (object $row): string => (string) $row->table_name,
            Illuminate\Support\Facades\DB::select(
                "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name"
            ),
        );
    };

    $report['tablesBefore'] = $listTables();

    $needManagementTables = ! in_array('management_periods', $report['tablesBefore'], true)
        || ! in_array('management_members', $report['tablesBefore'], true);

    if ($needManagementTables) {
        $repair = [];

        try {
            $code = Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            $repair['migrateExit'] = $code;
            $repair['migrateOutput'] = mb_substr(Illuminate\Support\Facades\Artisan::output(), 0, 1200);
        } catch (Throwable $e) {
            $repair['migrateThrew'] = mb_substr((string) $e, 0, 400);
            $repair['migrateRoot'] = $e->getPrevious() !== null ? mb_substr($e->getPrevious()->getMessage(), 0, 300) : null;
        }

        if (($repair['migrateExit'] ?? null) !== 0) {
            // Ujicoba fallback endpoint DIRECT (tanpa pooler) bila DATABASE_URL
            // memakai host -pooler.
            $rawUrl = (string) (getenv('DATABASE_URL') ?: '');
            $parts = parse_url($rawUrl) ?: [];
            $host = is_array($parts) ? ($parts['host'] ?? null) : null;

            if (is_string($host) && str_contains($host, '-pooler')) {
                $previousUrl = config('database.connections.pgsql.url');

                config(['database.connections.pgsql.url' => str_replace('-pooler.', '.', $rawUrl)]);
                Illuminate\Support\Facades\DB::purge('pgsql');

                try {
                    $code = Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                    $repair['directMigrateExit'] = $code;
                    $repair['directMigrateOutput'] = mb_substr(Illuminate\Support\Facades\Artisan::output(), 0, 1200);
                } catch (Throwable $e) {
                    $repair['directMigrateThrew'] = mb_substr((string) $e, 0, 400);
                    $repair['directMigrateRoot'] = $e->getPrevious() !== null ? mb_substr($e->getPrevious()->getMessage(), 0, 300) : null;
                } finally {
                    if ($previousUrl !== null) {
                        config(['database.connections.pgsql.url' => $previousUrl]);
                    }

                    Illuminate\Support\Facades\DB::purge('pgsql');
                }
            } else {
                $repair['directSkipped'] = 'DATABASE_URL tidak memakai host -pooler';
            }
        }

        $report['repair'] = $repair;
        $report['tablesAfter'] = $listTables();
    }

    $rowCount = static function (string $table): ?int {
        try {
            return (int) Illuminate\Support\Facades\DB::table($table)->count();
        } catch (Throwable) {
            return null; // tabel tidak ada
        }
    };

    $report['managementPeriodsCount'] = $rowCount('management_periods');
    $report['managementMembersCount'] = $rowCount('management_members');
} catch (Throwable $e) {
    $safe = static function (Throwable $e): string {
        $msg = preg_replace('/(pass|pwd|token)[^\s=&]*=[^\s&]*/i', '$1=***', $e->getMessage()) ?? $e->getMessage();
        $msg = preg_replace('/postgres([^:@\s]*)@/i', 'postgres***@', $msg) ?? $msg;

        return get_class($e).': '.substr($msg, 0, 400);
    };
    $report['error'] = $safe($e);
}

http_response_code(200);
header('Content-Type: application/json');
header('Cache-Control: no-store');
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
