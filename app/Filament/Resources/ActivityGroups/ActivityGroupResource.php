<?php

namespace App\Filament\Resources\ActivityGroups;

use App\Filament\Resources\ActivityGroups\Pages\CreateActivityGroup;
use App\Filament\Resources\ActivityGroups\Pages\EditActivityGroup;
use App\Filament\Resources\ActivityGroups\Pages\ListActivityGroups;
use App\Filament\Resources\ActivityGroups\Schemas\ActivityGroupForm;
use App\Filament\Resources\ActivityGroups\Tables\ActivityGroupsTable;
use App\Models\ActivityGroup;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ActivityGroupResource extends Resource
{
    protected static ?string $model = ActivityGroup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static UnitEnum|string|null $navigationGroup = 'Konten Situs';

    protected static ?string $navigationLabel = 'Kelompok Kegiatan';

    protected static ?string $modelLabel = 'Kelompok kegiatan';

    protected static ?string $pluralModelLabel = 'Kelompok kegiatan';

    protected static ?string $slug = 'kelompok-kegiatan';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return ActivityGroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActivityGroupsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityGroups::route('/'),
            'create' => CreateActivityGroup::route('/create'),
            'edit' => EditActivityGroup::route('/{record}/edit'),
        ];
    }
}
