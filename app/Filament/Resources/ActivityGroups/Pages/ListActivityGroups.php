<?php

namespace App\Filament\Resources\ActivityGroups\Pages;

use App\Filament\Resources\ActivityGroups\ActivityGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListActivityGroups extends ListRecords
{
    protected static string $resource = ActivityGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
