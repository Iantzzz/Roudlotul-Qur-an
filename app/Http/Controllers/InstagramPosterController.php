<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Menyajikan poster video Instagram dari domain sendiri.
 *
 * Endpoint media Instagram mengirim header Cross-Origin-Resource-Policy:
 * same-origin, sehingga gambarnya tidak bisa dipakai langsung sebagai <img>
 * di situs ini. Route ini mengambil gambar sekali, menyimpannya di cache,
 * lalu menyajikannya tanpa header pembatas tersebut.
 */
class InstagramPosterController extends Controller
{
    private const CACHE_TTL_SECONDS = 86400;

    public function show(string $code): Response
    {
        $cacheKey = 'instagram-poster:l:'.$code;
        $binary = null;

        try {
            $binary = Cache::get($cacheKey);
        } catch (Throwable) {
            $binary = null;
        }

        if (! is_string($binary) || $binary === '') {
            $binary = $this->fetch($code);

            if ($binary === null) {
                abort(Response::HTTP_NOT_FOUND);
            }

            rescue(fn () => Cache::put($cacheKey, $binary, self::CACHE_TTL_SECONDS));
        }

        return response($binary, Response::HTTP_OK, [
            'Content-Type' => 'image/jpeg',
            'Content-Length' => (string) strlen($binary),
            'Cache-Control' => 'public, max-age='.self::CACHE_TTL_SECONDS,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function fetch(string $code): ?string
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
                    'Accept' => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
                ])
                ->get('https://www.instagram.com/p/'.$code.'/media/?size=l');

            if (! $response->successful()) {
                return null;
            }

            if (! str_starts_with(strtolower((string) $response->header('Content-Type')), 'image/')) {
                return null;
            }

            $body = $response->body();

            return $body !== '' ? $body : null;
        } catch (Throwable) {
            return null;
        }
    }
}
