<?php

/**
 * Membangkitkan ulang aset favicon browser tanpa bingkai dari logo mentah:
 *   - favicon-32x32.png  : roster di kanvas TRANSPARAN
 *   - favicon.ico        : kontainer 16/32/48 px (PNG di dalam ICO)
 *   - apple-touch-icon.png : roster di latar PUTIH (iOS tidak mendukung
 *     transparan; tanpa tepi/nada tile)
 *
 * Ikon PWA (images/site/icon-192.png & icon-512.png) TIDAK disentuh —
 * tujuan maskable memerlukan latar penuh.
 *
 * Idempotent: boleh dijalankan berulang dari yurisplasi produksi.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit("jalankan lewat CLI\n");
}

$logosDir = __DIR__.'/../public';
$source = "{$logosDir}/images/site/logo-rq.png";
$pngTarget = "{$logosDir}/favicon-32x32.png";
$icoTarget = "{$logosDir}/favicon.ico";
$touchTarget = "{$logosDir}/apple-touch-icon.png";

$src = imagecreatefrompng($source);
if ($src === false) {
    exit("gagal memuat {$source}\n");
}

$srcWidth = imagesx($src);
$srcHeight = imagesy($src);
imagealphablending($src, false);
imagesavealpha($src, true);

// Bounding-box badge: piksel TAMPAK = tidak tersapu flood-fill TRANSPARAN yang
// dimulai dari tepi gambar. Skala GD: 0=opak … 127=transparan penuh — data
// logo ini biner (hanya alpha 0 dan 127), latar punya alpha=127 dengan
// rem-RGB gelap, sedangkan bagian putih DI DALAM pentagon opak (alpha=0)
// sehingga tidak tersapu dan tetap dihitung bagian badge. Uji warna putih
// sengaja TIDAK dipakai: putih di sini bagian artwork, bukan latar.
$isBackground = static function (int $x, int $y) use ($src): bool {
    return imagecolorsforindex($src, imagecolorat($src, $x, $y))['alpha'] >= 112;
};

$visited = [];
$queue = [];

for ($x = 0; $x < $srcWidth; $x++) {
    $queue[] = [$x, 0];
    $queue[] = [$x, $srcHeight - 1];
}
for ($y = 0; $y < $srcHeight; $y++) {
    $queue[] = [0, $y];
    $queue[] = [$srcWidth - 1, $y];
}

while ($queue !== []) {
    [$x, $y] = array_pop($queue);

    if ($x < 0 || $y < 0 || $x >= $srcWidth || $y >= $srcHeight) {
        continue;
    }

    $key = $y * $srcWidth + $x;

    if (isset($visited[$key]) || ! $isBackground($x, $y)) {
        continue;
    }

    $visited[$key] = true;
    $queue[] = [$x + 1, $y];
    $queue[] = [$x - 1, $y];
    $queue[] = [$x, $y + 1];
    $queue[] = [$x, $y - 1];
}

$minX = $srcWidth;
$minY = $srcHeight;
$maxX = -1;
$maxY = -1;

for ($y = 0; $y < $srcHeight; $y++) {
    for ($x = 0; $x < $srcWidth; $x++) {
        if (! isset($visited[$y * $srcWidth + $x])) {
            $minX = min($minX, $x);
            $minY = min($minY, $y);
            $maxX = max($maxX, $x);
            $maxY = max($maxY, $y);
        }
    }
}
if ($maxX < 0) {
    exit("badge tidak terdeteksi\n");
}

$badgeWidth = $maxX - $minX + 1;
$badgeHeight = $maxY - $minY + 1;
printf("sumber %dx%d | badge tampak %dx%d (%d,%d)\n", $srcWidth, $srcHeight, $badgeWidth, $badgeHeight, $minX, $minY);

/**
 * Buat kanvas ukuran tetap lalu gambar badge (pemangkasan) dipusatkan.
 */
$renderOn = function (int $size, ?array $background, float $paddingRatio) use ($src, $minX, $minY, $badgeWidth, $badgeHeight): GdImage {
    $dst = imagecreatetruecolor($size, $size);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);

    if ($background !== null) {
        [$r, $g, $b] = $background;
        imagefilledrectangle($dst, 0, 0, $size, $size, imagecolorallocate($dst, $r, $g, $b));
    } else {
        imagefilledrectangle($dst, 0, 0, $size, $size, imagecolorallocatealpha($dst, 0, 0, 0, 127));
    }

    imagealphablending($dst, true);

    $padding = max(1, (int) round($size * $paddingRatio));
    $box = $size - 2 * $padding;
    $scale = min($box / $badgeWidth, $box / $badgeHeight);
    $drawWidth = (int) max(1, round($badgeWidth * $scale));
    $drawHeight = (int) max(1, round($badgeHeight * $scale));

    imagecopyresampled(
        $dst,
        $src,
        (int) round(($size - $drawWidth) / 2),
        (int) round(($size - $drawHeight) / 2),
        $minX,
        $minY,
        $drawWidth,
        $drawHeight,
        $badgeWidth,
        $badgeHeight,
    );

    return $dst;
};

$pngBytes = function (GdImage $img): string {
    ob_start();
    imagepng($img, null, 9);
    $bytes = (string) ob_get_clean();
    imagedestroy($img);

    return $bytes;
};

// favicon-32x32.png — transparan penuh, tanpa bingkai apapun.
imagepng($renderOn(32, null, 0.05), $pngTarget, 9);

// apple-touch-icon.png — badge dipusatkan di latar putih penuh (tanpa tepi).
imagepng($renderOn(180, [255, 255, 255], 0.06), $touchTarget, 9);

// favicon.ico — 16/32/48 px sebagai entri PNG di dalam wadah ICO.
$sizes = [16, 32, 48];
$entries = '';
$body = '';
$offset = 6 + 16 * count($sizes);

foreach ($sizes as $size) {
    $bytes = $pngBytes($renderOn($size, null, max(0.05, 1 / $size)));
    $entries .= pack('CCCCvvVV', $size, $size, 0, 0, 1, 32, strlen($bytes), $offset);
    $body .= $bytes;
    $offset += strlen($bytes);
}

file_put_contents($icoTarget, pack('vvv', 0, 1, count($sizes)).$entries.$body);

foreach ([$pngTarget, $icoTarget, $touchTarget] as $file) {
    printf("%-28s %6d byte\n", basename($file), filesize($file));
}
printf("favicon selesai dibangkitkan tanpa bingkai.\n");
