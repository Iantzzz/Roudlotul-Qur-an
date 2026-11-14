<?php

namespace App\Filament\Resources\Concerns;

use App\Support\SiteMediaUploader;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Http\UploadedFile;

/**
 * Dipakai halaman Create/Edit resource konten yang menyimpan foto sebagai
 * site_media: mengubah unggahan Filament (storeFiles(false) → UploadedFile)
 * menjadi baris site_media, serta merapikan baris lama yang kehilangan
 * rujukan setelah foto diganti.
 */
trait ManagesSiteMediaUpload
{
    protected ?int $previousMediaId = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->applySiteMediaUpload($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->previousMediaId = $this->getRecord()->site_media_id;

        return $this->applySiteMediaUpload($data);
    }

    protected function afterSave(): void
    {
        parent::afterSave();

        SiteMediaUploader::deleteIfOrphan($this->previousMediaId);
        $this->previousMediaId = null;
    }

    protected function applySiteMediaUpload(array $data): array
    {
        $photo = $data['photo'] ?? null;
        $clear = (bool) ($data['clear_photo'] ?? false);

        unset($data['photo'], $data['clear_photo']);

        if ($photo instanceof UploadedFile) {
            $data['site_media_id'] = SiteMediaUploader::store($photo);
        } elseif ($clear) {
            $data['site_media_id'] = null;
        } elseif ($this instanceof EditRecord) {
            $data['site_media_id'] = $this->getRecord()->site_media_id;
        } else {
            $data['site_media_id'] = null;
        }

        return $data;
    }
}
