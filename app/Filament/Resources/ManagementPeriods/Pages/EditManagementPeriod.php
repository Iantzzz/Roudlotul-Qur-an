<?php

namespace App\Filament\Resources\ManagementPeriods\Pages;

use App\Filament\Resources\ManagementPeriods\ManagementPeriodResource;
use App\Models\ManagementPeriod;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditManagementPeriod extends EditRecord
{
    protected static string $resource = ManagementPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalHeading('Hapus periode kepengurusan')
                ->modalDescription(function (ManagementPeriod $record): string {
                    return sprintf(
                        'Periode "%s" memuat %d unit struktur dan %d anggota. Menghapus periode akan menghapus SEMUANYA secara permanen (unit dan anggota periode ini saja, periode lain tidak tersentuh).',
                        $record->label,
                        $record->units()->count(),
                        $record->members()->count(),
                    );
                })
                ->successRedirectUrl(ManagementPeriodResource::getUrl('index')),
        ];
    }
}
