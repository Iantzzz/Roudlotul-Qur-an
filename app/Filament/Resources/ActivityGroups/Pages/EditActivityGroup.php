<?php

namespace App\Filament\Resources\ActivityGroups\Pages;

use App\Filament\Resources\ActivityGroups\ActivityGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditActivityGroup extends EditRecord
{
    protected static string $resource = ActivityGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalHeading('Hapus kelompok kegiatan')
                ->modalDescription(function ($record): string {
                    return sprintf(
                        'Menghapus kelompok "%s" juga menghapus %d kegiatan di dalamnya secara permanen. Butir kegiatan lain (kartu beranda) tidak tersentuh.',
                        $record->title,
                        (int) $record->activities()->count(),
                    );
                }),
        ];
    }
}
