<?php

namespace App\Filament\Resources\ManagementPeriods\Pages;

use App\Filament\Resources\ManagementPeriods\ManagementPeriodResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListManagementPeriods extends ListRecords
{
    protected static string $resource = ManagementPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
