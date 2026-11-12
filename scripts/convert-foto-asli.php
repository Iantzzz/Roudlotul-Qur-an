<?php

/**
 * Konversi foto dokumentasi asli asrama menjadi aset web:
 *   - sumber  : folder unduhan foto mentah (JPG dari kamera/HP, 2–12 MB)
 *   - keluaran: public/images/site/*.webp — sisi panjang maks 1600 px,
 *     WebP kualitas 82, orientasi EXIF dipatahkan agar tampil tegak.
 *
 * Nama file dirapikan: "foto 1.jpg.jpeg" -> foto-1.webp,
 * "IMG_20240505_070755.jpg.jpeg" -> img-20240505-070755.webp.
 * Duplikat yang sudah ada di repo (IMG_20241017_052255) dilewati.
 *
 * Juga membangkitkan lembar kontak (contact sheet) berlabel nomor urut
 * ke folder sementara untuk membantu penulisan alt/caption.
 *
 * Pemakaian:
 *   php scripts/convert-foto-asli.php [folder_sumber]
 *
 * Idempotent: file keluaran yang sudah ada dilewati.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit("jalankan lewat CLI\n");
}

ini_set('memory_limit', '768M');

$sourceDir = $argv[1] ?? 'C:/Users/win 11/Downloads/foto-asli-situs';
$destDir = __DIR__ . '/../public/images/site';
$sheetDir = getenv('TEMP') !== false && getenv('TEMP') !== '' ? getenv('TEMP') . '/opencode' : sys_get_temp_dir() . '/opencode';

// Foto ini sudah dioptimalkan dan dipakai sebagai hero halaman kegiatan.
$skipBase = ['img-20241017-052255'];

// 4x2 lembar kontak: 8 thumbnail berlabel per lembar.
$thumbW = 380;
$thumbH = 285;
$perSheet = 8;

if (! is_dir($sourceDir)) {
    exit("folder sumber tidak ditemukan: {$sourceDir}\n");
}

if (! is_dir($sheetDir)) {
    mkdir($sheetDir, 0777, true);
}

$files = scandir($sourceDir) ?: [];
$files = array_values(array_filter($files, static fn (string $name): bool =>
    (bool) preg_match('/\.jpe?g$/i', $name)
));

sort($files, SORT_NATURAL | SORT_FLAG_CASE);

if ($files === []) {
    exit("tidak ada foto di folder sumber\n");
}

/** Rapikan nama: huruf kecil, spasi/underscore jadi tanda hubung, buang ekstensi ganda. */
$slug = static function (string $name): string {
    $base = preg_replace('/(\.jpg)?\.jpe?g$/i', '', $name) ?? $name;
    $base = strtolower(trim($base));
    $base = preg_replace('/[\s_]+/', '-', $base) ?? $base;
    $base = preg_replace('/-+/', '-', $base) ?? $base;

    return trim($base, '-');
};

/** Patahkan orientasi EXIF supaya foto tampil tegak seperti di galeri HP. */
$applyOrientation = static function (GdImage $image, int $orientation): GdImage {
    switch ($orientation) {
        case 2: imageflip($image, IMG_FLIP_HORIZONTAL); break;
        case 3: imageflip($image, IMG_FLIP_BOTH); break;
        case 4: imageflip($image, IMG_FLIP_VERTICAL); break;
        case 5:
            imageflip($image, IMG_FLIP_HORIZONTAL);
            $image = imagerotate($image, -90, 0) ?: $image;
            break;
        case 6:
            $image = imagerotate($image, -90, 0) ?: $image;
            break;
        case 7:
            imageflip($image, IMG_FLIP_HORIZONTAL);
            $image = imagerotate($image, 90, 0) ?: $image;
            break;
        case 8:
            $image = imagerotate($image, 90, 0) ?: $image;
            break;
        default:
            break; // 1 dan nilai tak dikenal: biarkan
    }

    return $image;
};

/** Skala sisi panjang ke target, tanpa pernah memperbesar. */
$resized = static function (GdImage $image, int $maxSide): array {
    $width = imagesx($image);
    $height = imagesy($image);
    $longest = max($width, $height);

    if ($longest <= $maxSide) {
        return [$image, $width, $height];
    }

    $scale = $maxSide / $longest;
    $newWidth = max(1, (int) round($width * $scale));
    $newHeight = max(1, (int) round($height * $scale));
    $target = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($target, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    imagedestroy($image);

    return [$target, $newWidth, $newHeight];
};

/** Thumbnail potong-penuh (cover) agar grid lembar kontak rapi. */
$coverCrop = static function (GdImage $image, int $boxW, int $boxH): GdImage {
    $width = imagesx($image);
    $height = imagesy($image);
    $scale = max($boxW / $width, $boxH / $height);
    $drawW = (int) round($width * $scale);
    $drawH = (int) round($height * $scale);
    $thumb = imagecreatetruecolor($boxW, $boxH);
    imagecopyresampled(
        $thumb,
        $image,
        (int) (($boxW - $drawW) / 2),
        (int) (($boxH - $drawH) / 2),
        0,
        0,
        $drawW,
        $drawH,
        $width,
        $height,
    );

    return $thumb;
};

$report = [];
$thumbs = [];

foreach ($files as $index => $name) {
    $base = $slug($name);

    if (in_array($base, $skipBase, true)) {
        printf("LEWATI %-38s (sudah ada di repo)\n", $name);
        continue;
    }

    $target = "{$destDir}/{$base}.webp";
    $ordinal = count($report) + 1;

    if (is_file($target)) {
        // Sudah pernah dikonversi: cukup catat dimensi dari file keluaran.
        $info = getimagesize($target);
        $report[] = ['ordinal' => $ordinal, 'name' => $name, 'base' => $base, 'width' => (int) ($info[0] ?? 0), 'height' => (int) ($info[1] ?? 0), 'bytes' => (int) filesize($target), 'skipped' => true];
        printf("ADA    %-38s -> %-30s\n", $name, basename($target));
    } else {
        $src = @imagecreatefromjpeg("{$sourceDir}/{$name}");
        if ($src === false) {
            printf("GAGAL  %-38s (tidak bisa didekode JPEG)\n", $name);
            continue;
        }

        $orientation = 1;
        $exif = @exif_read_data("{$sourceDir}/{$name}");
        if (is_array($exif) && isset($exif['Orientation']) && (int) $exif['Orientation'] >= 1) {
            $orientation = (int) $exif['Orientation'];
        }

        $src = $applyOrientation($src, $orientation);
        [$src, $width, $height] = $resized($src, 1600);

        if (! imagewebp($src, $target, 82)) {
            printf("GAGAL  %-38s (menulis WebP)\n", $name);
            imagedestroy($src);
            continue;
        }

        $bytes = (int) filesize($target);
        imagedestroy($src);

        $report[] = ['ordinal' => $ordinal, 'name' => $name, 'base' => $base, 'width' => $width, 'height' => $height, 'bytes' => $bytes, 'skipped' => false];
        printf("OK     %-38s -> %-30s %4dx%-4d %6.0f kB\n", $name, basename($target), $width, $height, $bytes / 1024);
    }

    // Kumpulkan thumbnail untuk lembar kontak (dari hasil akhir yang pasti ada).
    $thumbSrc = @imagecreatefromwebp($target);
    if ($thumbSrc !== false) {
        $thumbs[] = ['ordinal' => $ordinal, 'image' => $coverCrop($thumbSrc, $thumbW, $thumbH)];
    }
}

if ($report === []) {
    exit("tidak ada foto yang berhasil diproses\n");
}

// Lembar kontak: grid 4x2, badge nomor urut di pojok kiri tiap thumbnail.
$totalBytes = 0;
foreach (array_chunk($thumbs, $perSheet) as $sheetIndex => $chunk) {
    $cols = 4;
    $rows = (int) ceil(count($chunk) / $cols);
    $pad = 10;
    $labelW = 46;
    $labelH = 26;
    $sheetW = $cols * $thumbW + ($cols + 1) * $pad;
    $sheetH = $rows * $thumbH + ($rows + 1) * $pad;
    $sheet = imagecreatetruecolor($sheetW, $sheetH);
    imagefilledrectangle($sheet, 0, 0, $sheetW, $sheetH, imagecolorallocate($sheet, 24, 24, 24));

    foreach ($chunk as $i => $thumb) {
        $col = $i % $cols;
        $row = (int) ($i / $cols);
        $x = $pad + $col * ($thumbW + $pad);
        $y = $pad + $row * ($thumbH + $pad);
        imagecopy($sheet, $thumb['image'], $x, $y, 0, 0, $thumbW, $thumbH);
        imagedestroy($thumb['image']);

        imagefilledrectangle($sheet, $x, $y, $x + $labelW, $y + $labelH, imagecolorallocate($sheet, 255, 255, 255));
        imagestring($sheet, 5, $x + 8, $y + 5, sprintf('%02d', $thumb['ordinal']), imagecolorallocate($sheet, 0, 0, 0));
    }

    $sheetPath = sprintf('%s/kontak-%02d.png', $sheetDir, $sheetIndex + 1);
    imagepng($sheet, $sheetPath);
    imagedestroy($sheet);
    printf("lembar kontak: %s\n", $sheetPath);
}

foreach ($report as $item) {
    $totalBytes += $item['bytes'];
}

// Laporan lengkap untuk penyusunan config (ordinal|base|w|h|bytes).
$reportPath = "{$sheetDir}/laporan-foto-asli.txt";
$lines = array_map(static fn (array $item): string =>
    sprintf("%02d|%s|%d|%d|%d", $item['ordinal'], $item['base'], $item['width'], $item['height'], $item['bytes']), $report);
file_put_contents($reportPath, implode("\n", $lines) . "\n");

printf(
    "selesai: %d foto, total %.1f MB | laporan: %s\n",
    count($report),
    $totalBytes / 1048576,
    $reportPath,
);
