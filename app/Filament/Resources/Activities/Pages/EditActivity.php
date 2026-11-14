<?php

namespace App\Filament\Resources\Activities\Pages;

use App\Filament\Resources\Activities\ActivityResource;
use App\Filament\Resources\Concerns\ManagesSiteMediaUpload;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditActivity extends EditRecord
{
    use ManagesSiteMediaUpload;

    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalHeading('Hapus kegiatan')
                ->modalDescription('Kegiatan yang dihapus hilang dari halaman Kegiatan atau kartu beranda. Tindakan ini tidak bisa dibatalkan.'),
        ];
    }
}
