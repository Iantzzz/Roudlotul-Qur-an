<?php

namespace App\Support;

use App\Models\Activity;
use App\Models\GalleryItem;
use App\Models\Greeting;
use App\Models\SiteMedia;
use Illuminate\Http\UploadedFile;

/**
 * Menerima unggahan foto dari panel admin dan menyimpannya sebagai baris
 * site_media (base64) karena filesystem Vercel ephemeral. Juga membantu
 * merapikan baris media yang sudah tidak dirujuk konten mana pun.
 */
final class SiteMediaUploader
{
    public static function store(UploadedFile $file): int
    {
        $media = SiteMedia::create([
            'name' => (string) ($file->getClientOriginalName() ?: 'unggahan'),
            'mime' => (string) $file->getMimeType(),
            'data' => base64_encode((string) file_get_contents((string) $file->getRealPath())),
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
