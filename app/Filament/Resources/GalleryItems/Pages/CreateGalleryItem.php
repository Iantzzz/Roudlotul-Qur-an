<?php

namespace App\Filament\Resources\GalleryItems\Pages;

use App\Filament\Resources\Concerns\ManagesSiteMediaUpload;
use App\Filament\Resources\GalleryItems\GalleryItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGalleryItem extends CreateRecord
{
    use ManagesSiteMediaUpload;

    protected static string $resource = GalleryItemResource::class;
}
