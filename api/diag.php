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

    // Tambah: saat tabel users tidak ada, jalankan migrasi + seed di SINI
    // dan sertakan OUTPUT Artisan untuk melihat persis apa yang gagal.
    $report['repair'] = rqRepairNow();

    $tables = array_map(
        static fn (object $row): string => (string) $row->table_name,
        Illuminate\Support\Facades\DB::select(
            "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name"
        ),
    );
    $report['tables'] = $tables;

    // Tambah: bandingkan endpoint DIRECT (bukan pooler) memakai kredensial
    // yang sama — untuk memastikan apakah build menulis ke database berbeda.
    $rawUrl = (string) (getenv('DATABASE_URL') ?: '');
    $report['databaseUrlLen'] = strlen($rawUrl);
    if ($rawUrl !== '') {
        $parts = parse_url($rawUrl) ?: [];
        $host = is_array($parts) ? ($parts['host'] ?? null) : null;
        if (is_string($host) && str_contains($host, '-pooler')) {
            $report['poolerEndpoint'] = true;
            $directHost = str_replace('-pooler.', '.', $host);
            $dsn = sprintf(
                'pgsql:host=%s;port=%s;dbname=%s;sslmode=require;connect_timeout=10',
                $directHost,
                $parts['port'] ?? '5432',
                ltrim($parts['path'] ?? '', '/')
            );
            $report['directEndpoint'] = rqProbeDirect($dsn);
        }
    }

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

/**
 * Jalankan migrasi + seeder pada DB runtime bila tabel `users` tidak ada,
 * dan kembalikan output Artisan untuk telemetri.
 *
 * @return array<string, mixed>
 */
function rqRepairNow(): array
{
    $out = [];

    try {
        $missing = Illuminate\Support\Facades\DB::select(
            "SELECT NOT EXISTS (
                SELECT 1 FROM information_schema.tables
                WHERE table_schema = 'public' AND table_name = 'users'
            ) AS missing"
        )[0]->missing ?? true;
    } catch (Throwable $e) {
        return ['probeError' => substr((string) $e, 0, 200)];
    }

    $out['usersMissingBefore'] = (bool) $missing;
    if (! $missing) {
        return $out;
    }

    try {
        $code = Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $out['migrateExit'] = $code;
        $out['migrateOutput'] = mb_substr(Illuminate\Support\Facades\Artisan::output(), 0, 1800);
    } catch (Throwable $e) {
        $out['migrateThrew'] = substr((string) $e, 0, 500);
        $prev = $e->getPrevious();
        $out['migrateRoot'] = $prev === null ? null : substr($prev->getMessage(), 0, 400);
    }

    // Coba sekali lagi dengan koneksi ulang + endpoint DIRECT (tanpa
    // PgBouncer/pooler) — bila berhasil di direct, masalahnya adalah pooler.
    try {
        $rawUrl = (string) (getenv('DATABASE_URL') ?: '');
        $parts = parse_url($rawUrl) ?: [];
        $host = is_array($parts) ? ($parts['host'] ?? null) : null;
        if (is_string($host) && str_contains($host, '-pooler')) {
            $directUrl = str_replace('-pooler.', '.', (string)$rawUrl);
            $cfg = config('database.connections.pgsql') ?? [];
            if (isset($cfg['url'])) {
                config(['database.connections.pgsql.url' => $directUrl]);
            }
            Illuminate\Support\Facades\DB::purge('pgsql');
            $code = Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            $out['directMigrateExit'] = $code;
            $out['directMigrateOutput'] = mb_substr(Illuminate\Support\Facades\Artisan::output(), 0, 1200);
            Illuminate\Support\Facades\DB::purge('pgsql');
        }
    } catch (Throwable $e) {
        $out['directMigrateThrew'] = substr((string) $e, 0, 500);
        $prev = $e->getPrevious();
        $out['directMigrateRoot'] = $prev === null ? null : substr($prev->getMessage(), 0, 400);
    }

    try {
        $code = Illuminate\Support\Facades\Artisan::call(
            'db:seed',
            ['--class' => 'Database\\Seeders\\AdminUserSeeder', '--force' => true],
        );
        $out['seedExit'] = $code;
        $out['seedOutput'] = mb_substr(Illuminate\Support\Facades\Artisan::output(), 0, 400);
    } catch (Throwable $e) {
        $out['seedThrew'] = substr((string) $e, 0, 300);
    }

    return $out;
}

/**
 * Probe baca-saja ke endpoint DIRECT (bukan pooler). Melaporkan daftar
 * tabel di schema public — tanpa memaparkan kredensial.
 *
 * @return array{ok: true, publicTables: int, tables: list<string>}|array{ok: false, error: string}
 */
function rqProbeDirect(string $dsn): array
{
    try {
        $pdo = new PDO($dsn);
        $row = $pdo->query("SELECT count(*) FROM information_schema.tables WHERE table_schema = 'public'")->fetch(PDO::FETCH_NUM);
        $names = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name LIMIT 30")->fetchAll(PDO::FETCH_COLUMN);

        return ['ok' => true, 'publicTables' => (int) $row[0], 'tables' => $names];
    } catch (Throwable $e) {
        $msg = preg_replace('/(user|password)=[^;]*/i', '$1=***', $e->getMessage()) ?? $e->getMessage();

        return ['ok' => false, 'error' => substr($msg, 0, 250)];
    }
}
