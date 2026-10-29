<?php

/**
 * SEMENTARA (diagnosis deployment): mencetak info database yang terlihat
 * oleh proses BUILD ke log build Vercel. Hanya host/nama database + boolean
 * keberadaan variabel — tanpa nilai rahasia.
 * Hapus file ini beserta barisnya di composer.json setelah selesai.
 */

declare(strict_types=1);

$env = static function (string $name): string {
    $value = getenv($name);

    return is_string($value) ? trim($value) : '';
};

// Urutannya sama persis dengan config/database.php bagian pgsql:
// DB_URL, lalu DATABASE_URL, lalu POSTGRES_URL.
$url = $env('DB_URL') ?: $env('DATABASE_URL') ?: $env('POSTGRES_URL');

$host = '(tidak ada URL database)';
$database = '';
if ($url !== '') {
    $parsed = parse_url($url);
    $host = $parsed['host'] ?? '(host tidak terbaca)';
    $database = ltrim($parsed['path'] ?? '', '/');
}

$pooler = str_contains($host, '-pooler');

printf(
    'BUILD_DB database=%s | dbUrl=%s databaseUrl=%s databaseUrlLen=%d postgresUrl=%s poolerHost=%s | adminEmail=%s adminPassword=%s',
    $database !== '' ? $database : '(kosong)',
    $env('DB_URL') !== '' ? 'ya' : 'tidak',
    $env('DATABASE_URL') !== '' ? 'ya' : 'tidak',
    strlen($url),
    $env('POSTGRES_URL') !== '' ? 'ya' : 'tidak',
    $pooler ? 'ya' : 'tidak',
    $env('ADMIN_EMAIL') !== '' ? 'ya' : 'tidak',
    $env('ADMIN_PASSWORD') !== '' ? 'ya' : 'tidak',
);
echo PHP_EOL;
