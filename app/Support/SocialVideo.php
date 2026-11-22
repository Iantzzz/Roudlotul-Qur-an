<?php

namespace App\Support;

/**
 * Menormalkan tautan video/media sosial menjadi data siap render.
 *
 * Platform yang di-embed langsung: YouTube, TikTok, Facebook, dan Vimeo.
 * Instagram dirender sebagai kartu poster yang membuka postingan di tab baru:
 * embed resminya menutupi tombol putar dengan CTA "Lihat lainnya di Instagram"
 * dan tidak mendukung autoplay. Platform lain (mis. X/Twitter atau LinkedIn)
 * tetap dikembalikan sebagai tautan keluar agar tidak hilang dari halaman.
 */
class SocialVideo
{
    private const YOUTUBE_ID_PATTERN = '[A-Za-z0-9_-]{11}';

    /**
     * @return array{
     *     platform: string,
     *     label: string,
     *     icon: string,
     *     embed_url: ?string,
     *     aspect: string,
     *     url: string,
     *     mode: string,
     *     poster_url: ?string
     * }|null
     */
    public static function parse(?string $input): ?array
    {
        $raw = trim((string) $input);

        if ($raw === '') {
            return null;
        }

        // ID YouTube mentah tanpa tautan lengkap.
        if (preg_match('/^'.self::YOUTUBE_ID_PATTERN.'$/', $raw) === 1) {
            return self::youtubeResult($raw, 'https://www.youtube.com/watch?v='.$raw);
        }

        $url = self::normalize($raw);

        if ($url === null) {
            return null;
        }

        $parts = parse_url($url);

        if ($parts === false || empty($parts['host'])) {
            return null;
        }

        $host = strtolower((string) $parts['host']);
        $host = preg_replace('/^www\./', '', $host) ?? $host;
        $path = (string) ($parts['path'] ?? '');

        parse_str((string) ($parts['query'] ?? ''), $query);

        return self::youtube($host, $path, $query, $url)
            ?? self::instagram($host, $path, $url)
            ?? self::tiktok($host, $path, $url)
            ?? self::facebook($host, $path, $url)
            ?? self::vimeo($host, $path, $url)
            ?? self::fallback($host, $url);
    }

    /**
     * URL embed untuk tampilan publik; null bila platform tidak mendukung
     * embed atau tautan tidak valid.
     */
    public static function embedUrl(?string $input): ?string
    {
        return self::parse($input)['embed_url'] ?? null;
    }

    /**
     * Tambahkan skema https:// bila belum ada, lalu validasi URL http(s).
     */
    public static function normalize(?string $input): ?string
    {
        $input = trim((string) $input);

        if ($input === '') {
            return null;
        }

        if (preg_match('#^[a-z][a-z0-9+.-]*://#i', $input) !== 1) {
            $input = 'https://'.$input;
        }

        $parts = parse_url($input);

        if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
            return null;
        }

        if (! in_array(strtolower((string) $parts['scheme']), ['http', 'https'], true)) {
            return null;
        }

        $host = strtolower((string) $parts['host']);

        if ($host !== 'localhost' && ! str_contains($host, '.')) {
            return null;
        }

        return $input;
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>|null
     */
    private static function youtube(string $host, string $path, array $query, string $url): ?array
    {
        if (! in_array($host, ['youtube.com', 'm.youtube.com', 'music.youtube.com', 'youtu.be', 'youtube-nocookie.com'], true)) {
            return null;
        }

        // watch?v=ID — parameter lain (list=, si=, t=) diabaikan.
        if (isset($query['v']) && is_string($query['v']) && preg_match('/^'.self::YOUTUBE_ID_PATTERN.'$/', $query['v']) === 1) {
            return self::youtubeResult($query['v'], $url);
        }

        // youtu.be/ID, shorts/ID, embed/ID, live/ID, watch/ID.
        $segments = self::segments($path);
        $candidate = $segments[1] ?? ($host === 'youtu.be' ? ($segments[0] ?? null) : null);

        if (is_string($candidate) && preg_match('/^'.self::YOUTUBE_ID_PATTERN.'$/', $candidate) === 1) {
            return self::youtubeResult($candidate, $url);
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function instagram(string $host, string $path, string $url): ?array
    {
        if (! in_array($host, ['instagram.com', 'instagr.am'], true)) {
            return null;
        }

        $segments = self::segments($path);
        $type = null;
        $code = null;

        foreach ($segments as $index => $segment) {
            if (in_array($segment, ['p', 'reel', 'reels', 'tv'], true)) {
                $type = $segment;
                $code = $segments[$index + 1] ?? null;

                break;
            }
        }

        if (! is_string($type) || ! is_string($code) || preg_match('/^[A-Za-z0-9_-]+$/', $code) !== 1) {
            return null;
        }

        $embedType = $type === 'reels' ? 'reel' : $type;
        $aspect = match ($type) {
            'reel', 'reels' => 'vertical',
            'tv' => 'landscape',
            default => 'portrait',
        };

        return self::result(
            'instagram',
            'Instagram',
            'instagram',
            "https://www.instagram.com/{$embedType}/{$code}/embed/",
            $aspect,
            $url,
            'facade',
            route('instagram.poster', ['code' => $code]),
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function tiktok(string $host, string $path, string $url): ?array
    {
        if (in_array($host, ['vm.tiktok.com', 'vt.tiktok.com'], true)) {
            // Tautan pendek perlu redirect; tampilkan sebagai tautan keluar.
            return self::result('tiktok', 'TikTok', 'tiktok', null, 'vertical', $url);
        }

        if (! in_array($host, ['tiktok.com', 'm.tiktok.com'], true)) {
            return null;
        }

        $segments = self::segments($path);

        // /embed/v2/{id}
        if (($segments[0] ?? null) === 'embed' && ($segments[1] ?? null) === 'v2') {
            $id = $segments[2] ?? null;

            if (is_string($id) && preg_match('/^\d+$/', $id) === 1) {
                return self::tiktokResult($id, $url);
            }
        }

        // /@user/video/{id} atau /@user/photo/{id}
        foreach ($segments as $index => $segment) {
            if (in_array($segment, ['video', 'photo'], true)) {
                $id = $segments[$index + 1] ?? null;

                if (is_string($id) && preg_match('/^\d+$/', $id) === 1) {
                    return self::tiktokResult($id, $url);
                }
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function facebook(string $host, string $path, string $url): ?array
    {
        if (! in_array($host, ['facebook.com', 'm.facebook.com', 'fb.com', 'fb.watch'], true)) {
            return null;
        }

        // Tautan pendek tidak bisa diandalkan oleh plugin embed.
        if ($host === 'fb.watch' || str_contains($path, '/share/')) {
            return self::result('facebook', 'Facebook', 'facebook', null, 'landscape', $url);
        }

        $isVideo = preg_match('#/videos/\d+#', $path) === 1
            || preg_match('#/reel/\d+#', $path) === 1
            || str_starts_with($path, '/watch')
            || str_contains($path, '/video.php');

        if (! $isVideo) {
            return null;
        }

        return self::result(
            'facebook',
            'Facebook',
            'facebook',
            'https://www.facebook.com/plugins/video.php?href='.rawurlencode($url).'&show_text=false&width=480',
            'landscape',
            $url,
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function vimeo(string $host, string $path, string $url): ?array
    {
        if (! in_array($host, ['vimeo.com', 'player.vimeo.com'], true)) {
            return null;
        }

        $segments = self::segments($path);
        $id = null;
        $hash = null;

        if ($host === 'player.vimeo.com') {
            if (($segments[0] ?? null) !== 'video') {
                return null;
            }

            $id = $segments[1] ?? null;
            $hash = $segments[2] ?? null;
        } else {
            $id = $segments[0] ?? null;
            $hash = $segments[1] ?? null;

            // /channels/{channel}/{id}
            if ($id === 'channels') {
                $id = $segments[2] ?? null;
                $hash = null;
            }

            // /groups/{group}/videos/{id}
            if (($videoIndex = array_search('videos', $segments, true)) !== false) {
                $id = $segments[$videoIndex + 1] ?? null;
                $hash = null;
            }
        }

        if (! is_string($id) || preg_match('/^\d+$/', $id) !== 1) {
            return null;
        }

        $embed = 'https://player.vimeo.com/video/'.$id;

        // Video unlisted memakai hash di segmen berikutnya.
        if (is_string($hash) && preg_match('/^[A-Za-z0-9]+$/', $hash) === 1) {
            $embed .= '?h='.$hash;
        }

        return self::result('vimeo', 'Vimeo', 'play', $embed, 'landscape', $url);
    }

    /**
     * @return array<string, mixed>
     */
    private static function fallback(string $host, string $url): array
    {
        return self::result(
            self::platformSlug($host),
            self::platformLabel($host),
            self::platformIcon($host),
            null,
            'landscape',
            $url,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private static function youtubeResult(string $id, string $url): array
    {
        return self::result(
            'youtube',
            'YouTube',
            'youtube',
            'https://www.youtube-nocookie.com/embed/'.$id,
            'landscape',
            $url,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private static function tiktokResult(string $id, string $url): array
    {
        return self::result(
            'tiktok',
            'TikTok',
            'tiktok',
            'https://www.tiktok.com/embed/v2/'.$id,
            'vertical',
            $url,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private static function result(
        string $platform,
        string $label,
        string $icon,
        ?string $embedUrl,
        string $aspect,
        string $url,
        ?string $mode = null,
        ?string $posterUrl = null,
    ): array {
        return [
            'platform' => $platform,
            'label' => $label,
            'icon' => $icon,
            'embed_url' => $embedUrl,
            'aspect' => $aspect,
            'url' => $url,
            'mode' => $mode ?? ($embedUrl === null ? 'link' : 'embed'),
            'poster_url' => $posterUrl,
        ];
    }

    /**
     * @return list<string>
     */
    private static function segments(string $path): array
    {
        return array_values(array_filter(
            explode('/', $path),
            fn (string $segment): bool => $segment !== '',
        ));
    }

    private static function platformSlug(string $host): string
    {
        return match (true) {
            str_contains($host, 'youtube') || $host === 'youtu.be' => 'youtube',
            str_contains($host, 'instagram') || $host === 'instagr.am' => 'instagram',
            str_contains($host, 'tiktok') => 'tiktok',
            str_contains($host, 'facebook') || in_array($host, ['fb.watch', 'fb.com'], true) => 'facebook',
            str_contains($host, 'vimeo') => 'vimeo',
            $host === 'x.com' || str_contains($host, 'twitter') => 'x',
            str_contains($host, 'linkedin') => 'linkedin',
            default => 'link',
        };
    }

    private static function platformLabel(string $host): string
    {
        return match (self::platformSlug($host)) {
            'youtube' => 'YouTube',
            'instagram' => 'Instagram',
            'tiktok' => 'TikTok',
            'facebook' => 'Facebook',
            'vimeo' => 'Vimeo',
            'x' => 'X',
            'linkedin' => 'LinkedIn',
            default => $host,
        };
    }

    private static function platformIcon(string $host): string
    {
        return match (self::platformSlug($host)) {
            'youtube' => 'youtube',
            'instagram' => 'instagram',
            'tiktok' => 'tiktok',
            'facebook' => 'facebook',
            default => 'play',
        };
    }
}
