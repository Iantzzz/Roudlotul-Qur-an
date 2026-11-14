<?php

namespace App\Filament\Resources\GalleryItems\Pages;

use App\Filament\Resources\Concerns\ManagesSiteMediaUpload;
use App\Filament\Resources\GalleryItems\GalleryItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGalleryItem extends EditRecord
{
    use ManagesSiteMediaUpload;

    protected static string $resource = GalleryItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalHeading('Hapus foto galeri')
                ->modalDescription('Foto yang dihapus hilang dari halaman Galeri dan strip galeri di beranda. Tindakan ini tidak bisa dibatalkan.'),
        ];
    }
}
