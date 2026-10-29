<?php

/**
 * Entrypoint serverless function untuk deployment Vercel (runtime vercel-php).
 *
 * Tiga luaran untuk setiap permintaan:
 *   1. Asset publik yang masuk daftar (allowlist) di bawah → dilayani langsung
 *      dari folder public/: Content-Type benar, ETag/If-None-Match (304),
 *      nosniff, Cache-Control publik, dan tanpa cookie.
 *   2. Path di bawah prefix asset yang tidak masuk daftar → 404 dengan badan
 *      kosong, tidak pernah diteruskan ke Laravel.
 *   3. Path di luar sistem asset → diteruskan ke aplikasi Laravel
 *      (public/index.php) dengan metode HTTP apa pun.
 */

declare(strict_types=1);

use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;

require_once __DIR__.'/../vendor/autoload.php';

// vercel-php meneruskan request HTTPS ke PHP melalui HTTP loopback;
// percayai header X-Forwarded-* dari proxy tersebut agar Laravel
// menghasilkan URL https untuk asset dan redirect.
TrustProxies::at(['127.0.0.1', '::1']);
TrustProxies::withHeaders(Request::HEADER_X_FORWARDED_PROTO | Request::HEADER_X_FORWARDED_PORT);

const RQ_MIME_TYPES = [
    'css' => 'text/css',
    'js' => 'application/javascript',
    'mjs' => 'application/javascript',
    'json' => 'application/json',
    'map' => 'application/json',
    'webp' => 'image/webp',
    'avif' => 'image/avif',
    'png' => 'image/png',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif' => 'image/gif',
    'svg' => 'image/svg+xml',
    'ico' => 'image/x-icon',
    'woff' => 'font/woff',
    'woff2' => 'font/woff2',
    'ttf' => 'font/ttf',
    'otf' => 'font/otf',
    'eot' => 'application/vnd.ms-fontobject',
    'txt' => 'text/plain',
    'xml' => 'application/xml',
    'pdf' => 'application/pdf',
    'html' => 'text/html',
];

const RQ_ASSET_PREFIXES = ['css', 'js', 'images', 'fonts'];

const RQ_ASSET_ROOT_FILES = [
    '/favicon.ico' => 'image/x-icon',
    '/favicon-32x32.png' => 'image/png',
    '/apple-touch-icon.png' => 'image/png',
    '/robots.txt' => 'text/plain',
    '/manifest.json' => 'application/json',
    '/sw.js' => 'application/javascript',
];

const RQ_ASSET_ALLOWLIST = [
    '#^/css/site\.css$#',
    '#^/css/filament/.+#',
    '#^/js/site\.js$#',
    '#^/js/filament/.+#',
    '#^/images/.+#',
    '#^/fonts/filament/.+#',
];

$path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

$outcome = rqAsset(__DIR__.'/../public', $path);

if ($outcome[0] === 'laravel') {
    require __DIR__.'/../public/index.php';

    return;
}

if ($outcome[0] === 'not_found') {
    http_response_code(404);

    return;
}

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
[$file, $mime] = [$outcome[1], $outcome[2]];
$etag = '"'.md5_file($file).'"';

if (($_SERVER['HTTP_IF_NONE_MATCH'] ?? null) === $etag) {
    http_response_code(304);
    header('X-Content-Type-Options: nosniff');

    return;
}

if (! in_array($method, ['GET', 'HEAD'], true)) {
    http_response_code(405);
    header('Allow: GET, HEAD');

    return;
}

http_response_code(200);
header('Content-Type: '.$mime);
header('Cache-Control: public, max-age=3600');
header('X-Content-Type-Options: nosniff');
header('ETag: '.$etag);
header('Content-Length: '.filesize($file));

if ($method === 'GET') {
    readfile($file);
}

/**
 * Mengklasifikasikan permintaan: dilayani sebagai asset, ditolak 404, atau
 * diteruskan ke Laravel. File dilayani hanya bila:
 *   - berada di bawah prefix asset (`/css/`, `/js/`, `/images/`, `/fonts/`)
 *     atau tercantum eksplisit sebagai file root (favicon, robots.txt);
 *   - lolos pemeriksaan traversal `..`, backslash, dan byte NUL pada bentuk
 *     dekode tunggal maupun ganda (double-encoding);
 *   - ekstensinya ada pada peta MIME (sehingga skrip PHP, phar, dsb. selalu
 *     ditolak);
 *   - cocok dengan daftar allowlist dan benar-benar berupa file (bukan
 *     direktori).
 *
 * @return array{0: 'laravel'}|array{0: 'not_found'}|array{0: 'serve', 1: string, 2: string}
 */
function rqAsset(string $root, string $path): array
{
    $prefix = '';

    foreach (RQ_ASSET_PREFIXES as $candidate) {
        if (str_starts_with($path, '/'.$candidate.'/')) {
            $prefix = '/'.$candidate.'/';

            break;
        }
    }

    $rootFile = RQ_ASSET_ROOT_FILES[$path] ?? null;

    if ($prefix === '' && $rootFile === null) {
        return ['laravel'];
    }

    foreach ([rawurldecode($path), rawurldecode(rawurldecode($path))] as $unpacked) {
        if (str_contains($unpacked, "\0") || str_contains($unpacked, '\\') || str_contains($unpacked, '..')) {
            return ['not_found'];
        }
    }

    $decoded = rawurldecode($path);

    if (isset(RQ_ASSET_ROOT_FILES[$decoded]) && is_file($root.$decoded)) {
        return ['serve', $root.$decoded, RQ_ASSET_ROOT_FILES[$decoded]];
    }

    $extension = strtolower(pathinfo($decoded, PATHINFO_EXTENSION));

    if (! isset(RQ_MIME_TYPES[$extension])) {
        return ['not_found'];
    }

    foreach (RQ_ASSET_ALLOWLIST as $pattern) {
        if (preg_match($pattern, $decoded) && is_file($root.$decoded)) {
            return ['serve', $root.$decoded, RQ_MIME_TYPES[$extension]];
        }
    }

    return ['not_found'];
}
