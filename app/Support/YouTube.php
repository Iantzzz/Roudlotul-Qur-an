<?php

namespace App\Support;

/**
 * Menormalkan tautan YouTube menjadi URL embed siap pakai.
 *
 * Format yang dikenali:
 *  - https://youtu.be/ID (termasuk dengan parameter seperti ?si=...)
 *  - https://www.youtube.com/watch?v=ID (parameter lain diabaikan)
 *  - https://youtube.com/shorts/ID
 *  - https://www.youtube.com/embed/ID
 *  - https://www.youtube.com/live/ID
 *  - ID mentah 11 karakter
 *
 * Tautan di luar format di atas (termasuk domain selain YouTube) mengembalikan
 * null agar halaman publik tidak mencoba merender embed yang tidak valid.
 */
class YouTube
{
    private const ID_PATTERN = '[A-Za-z0-9_-]{11}';

    public static function embedUrl(?string $input): ?string
    {
        $input = trim((string) $input);

        if ($input === '') {
            return null;
        }

        // ID langsung tanpa tautan lengkap.
        if (preg_match('/^'.self::ID_PATTERN.'$/', $input) === 1) {
            return self::embed($input);
        }

        // Terima tautan tanpa skema, mis. "youtu.be/ID".
        if (! str_contains($input, '://')) {
            $input = 'https://'.$input;
        }

        $parts = parse_url($input);

        if ($parts === false || empty($parts['host'])) {
            return null;
        }

        $host = strtolower((string) $parts['host']);
        $host = preg_replace('/^www\./', '', $host) ?? $host;

        if (! in_array($host, ['youtube.com', 'youtu.be', 'm.youtube.com', 'youtube-nocookie.com'], true)) {
            return null;
        }

        // watch?v=ID — parameter lain (list=, si=, t=) diabaikan.
        parse_str((string) ($parts['query'] ?? ''), $query);

        if (isset($query['v']) && is_string($query['v']) && preg_match('/^'.self::ID_PATTERN.'$/', $query['v']) === 1) {
            return self::embed($query['v']);
        }

        // youtu.be/ID, shorts/ID, embed/ID, live/ID, watch/ID.
        $segments = array_values(array_filter(explode('/', (string) ($parts['path'] ?? '')), fn (string $segment): bool => $segment !== ''));
        $candidate = $segments[1] ?? ($host === 'youtu.be' ? ($segments[0] ?? null) : null);

        if (is_string($candidate) && preg_match('/^'.self::ID_PATTERN.'$/', $candidate) === 1) {
            return self::embed($candidate);
        }

        return null;
    }

    private static function embed(string $id): string
    {
        return 'https://www.youtube-nocookie.com/embed/'.$id;
    }
}
