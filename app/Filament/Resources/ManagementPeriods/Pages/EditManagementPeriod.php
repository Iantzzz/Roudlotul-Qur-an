<?php

namespace App\Filament\Resources\ManagementPeriods\Pages;

use App\Filament\Resources\ManagementPeriods\ManagementPeriodResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditManagementPeriod extends EditRecord
{
    protected static string $resource = ManagementPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
