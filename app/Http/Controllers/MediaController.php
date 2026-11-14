<?php

namespace App\Http\Controllers;

use App\Models\SiteMedia;
use Illuminate\Http\Response;

/**
 * Menyajikan foto hasil unggahan admin dari tabel site_media. Konten baris
 * site_media tidak berubah setelah dibuat (mengganti foto berarti baris
 * baru dengan id baru), sehingga respons aman diberi header cache agresif.
 */
class MediaController extends Controller
{
    public function show(SiteMedia $media): Response
    {
        $binary = base64_decode((string) $media->data, true);

        if ($binary === false) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return response($binary, Response::HTTP_OK, [
            'Content-Type' => (string) $media->mime,
            'Content-Length' => (string) strlen($binary),
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'ETag' => sprintf('"media-%d"', (int) $media->getKey()),
        ]);
    }
}
