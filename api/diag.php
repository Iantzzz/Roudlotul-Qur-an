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
        try {
            $code = Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            $report['repair'] = [
                'migrateExit' => $code,
                'migrateOutput' => mb_substr(Illuminate\Support\Facades\Artisan::output(), 0, 1800),
            ];
        } catch (Throwable $e) {
            $report['repair'] = [
                'migrateThrew' => mb_substr((string) $e, 0, 500),
                'migrateRoot' => $e->getPrevious() !== null ? mb_substr($e->getPrevious()->getMessage(), 0, 400) : null,
            ];
        }

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
