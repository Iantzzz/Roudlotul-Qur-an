<?php

/**
 * SEMENTARA: endpoint diagnostik koneksi database produksi.
 * Hanya melaporkan fakta non-rahasia (booleans, versi, nama class exception).
 * Hapus file ini setelah diagnosis selesai.
 */

declare(strict_types=1);

$safe = static function (Throwable $e): string {
    $msg = preg_replace('/(pass|pwd)[^\s=&]*=[^\s&]*/i', '$1=***', $e->getMessage()) ?? $e->getMessage();

    return get_class($e).': '.substr($msg, 0, 400);
};

$report = [
    'php' => PHP_VERSION,
    'pdoDrivers' => PDO::getAvailableDrivers(),
    'dbConnection' => env('DB_CONNECTION'),
    'hasDatabaseUrl' => ! empty(env('DATABASE_URL')),
    'databaseUrlLen' => strlen((string) env('DATABASE_URL')),
    'hasPostgresUrl' => ! empty(env('POSTGRES_URL')),
];

try {
    require_once __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    $pdo = Illuminate\Support\Facades\DB::connection()->getPdo();
    $report['db'] = ['ok' => true, 'serverVersion' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION)];
} catch (Throwable $e) {
    $report['db'] = ['ok' => false, 'error' => $safe($e)];
}

http_response_code(200);
header('Content-Type: application/json; charset=utf-8');
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
