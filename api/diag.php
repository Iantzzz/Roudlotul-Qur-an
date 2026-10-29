<?php

/**
 * SEMENTARA: endpoint diagnostik database untuk runtime Vercel.
 * Melaporkan fakta NON-rahasia saja: driver/host/nama database,
 * daftar tabel, hitungan baris, dan boolean keberadaan env (tanpa nilai).
 * Hapus file ini setelah diagnosis selesai.
 */

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

$report = [
    'php' => PHP_VERSION,
];

try {
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    $report['laravel'] = app()->version();

    $env = static function (string $name): bool {
        return trim((string) (getenv($name) ?: '')) !== '';
    };

    $report['env'] = [
        'hasDbUrl' => $env('DB_URL'),
        'hasDatabaseUrl' => $env('DATABASE_URL'),
        'hasPostgresUrl' => $env('POSTGRES_URL'),
        'hasAdminEmail' => $env('ADMIN_EMAIL'),
        'hasAdminPassword' => $env('ADMIN_PASSWORD'),
        'hasAdminName' => $env('ADMIN_NAME'),
    ];

    $conn = Illuminate\Support\Facades\DB::connection();
    $cfg = $conn->getConfig();

    // Whitelist eksplisit — 'url' dan 'password' tidak boleh ikut keluar.
    $report['connection'] = [
        'name' => (string) config('database.default'),
        'driver' => $cfg['driver'] ?? null,
        'host' => $cfg['host'] ?? null,
        'port' => $cfg['port'] ?? null,
        'database' => $cfg['database'] ?? null,
        'sslmode' => $cfg['sslmode'] ?? null,
    ];

    $tables = array_map(
        static fn (object $row): string => (string) $row->table_name,
        Illuminate\Support\Facades\DB::select(
            "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name"
        ),
    );
    $report['tables'] = $tables;

    $rowCount = static function (string $table) use ($conn): ?int {
        try {
            return (int) $conn->table($table)->count();
        } catch (Throwable) {
            return null; // tabel tidak ada
        }
    };

    $report['usersCount'] = $rowCount('users');
    $report['migrationsCount'] = $rowCount('migrations');

    $adminEmail = trim((string) (getenv('ADMIN_EMAIL') ?: ''));
    if ($adminEmail !== '') {
        try {
            $report['adminUserExists'] = Illuminate\Support\Facades\DB::table('users')
                ->where('email', $adminEmail)
                ->exists();
        } catch (Throwable) {
            $report['adminUserExists'] = null;
        }
    }
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
