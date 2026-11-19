<?php

namespace App\Support;

use App\Models\Activity;
use App\Models\GalleryItem;
use App\Models\Greeting;
use App\Models\SiteMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

/**
 * Menerima unggahan foto dari panel admin dan menyimpannya sebagai baris
 * site_media (base64) karena filesystem Vercel ephemeral. Juga membantu
 * merapikan baris media yang sudah tidak dirujuk konten mana pun.
 */
final class SiteMediaUploader
{
    public static function store(UploadedFile $file): int
    {
        $realPath = (string) $file->getRealPath();
        $raw = ($realPath !== '' && is_file($realPath)) ? @file_get_contents($realPath) : false;

        if ($raw === false || $raw === '') {
            // File temp tidak ada (mis. instance serverless berbeda) — jangan
            // simpan base64 kosong; tampilkan pesan yang jelas di kolom foto.
            throw ValidationException::withMessages([
                'photo' => 'Foto gagal diproses — mohon pilih ulang foto lalu Simpan kembali.',
            ]);
        }

        $media = SiteMedia::create([
            'name' => (string) ($file->getClientOriginalName() ?: 'unggahan'),
            'mime' => (string) $file->getMimeType(),
            'data' => base64_encode($raw),
            'size' => (int) $file->getSize(),
        ]);

        return (int) $media->getKey();
    }

    /**
     * Hapus baris media bila sudah tidak dirujuk tabel konten mana pun.
     * Aman dipanggil untuk id null; media yang masih terpakai tidak dihapus.
     */
    public static function deleteIfOrphan(?int $mediaId): void
    {
        if ($mediaId === null) {
            return;
        }

        $referenced = Greeting::query()->where('site_media_id', $mediaId)->exists()
            || Activity::query()->where('site_media_id', $mediaId)->exists()
            || GalleryItem::query()->where('site_media_id', $mediaId)->exists();

        if (! $referenced) {
            SiteMedia::query()->whereKey($mediaId)->delete();
        }
    }
}
