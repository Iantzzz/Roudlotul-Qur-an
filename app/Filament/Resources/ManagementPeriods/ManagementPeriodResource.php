<?php

namespace App\Filament\Resources\ManagementPeriods;

use App\Filament\Resources\ManagementPeriods\Pages\CreateManagementPeriod;
use App\Filament\Resources\ManagementPeriods\Pages\EditManagementPeriod;
use App\Filament\Resources\ManagementPeriods\Pages\ListManagementPeriods;
use App\Filament\Resources\ManagementPeriods\RelationManagers\MembersRelationManager;
use App\Filament\Resources\ManagementPeriods\Schemas\ManagementPeriodForm;
use App\Filament\Resources\ManagementPeriods\Tables\ManagementPeriodsTable;
use App\Models\ManagementPeriod;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ManagementPeriodResource extends Resource
{
    protected static ?string $model = ManagementPeriod::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Kepengurusan';

    protected static ?string $modelLabel = 'Periode kepengurusan';

    protected static ?string $pluralModelLabel = 'Periode kepengurusan';

    protected static ?string $slug = 'kepengurusan';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ManagementPeriodForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ManagementPeriodsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            MembersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListManagementPeriods::route('/'),
            'create' => CreateManagementPeriod::route('/create'),
            'edit' => EditManagementPeriod::route('/{record}/edit'),
        ];
    }
}
