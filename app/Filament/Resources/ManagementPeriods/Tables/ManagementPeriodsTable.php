<?php

namespace App\Filament\Resources\ManagementPeriods\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ManagementPeriodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->label('Periode')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('members_count')
                    ->counts('members')
                    ->label('Jumlah anggota')
                    ->formatStateUsing(fn ($state): string => (string) $state)
                    ->alignCenter(),

                ToggleColumn::make('is_active')
                    ->label('Aktif'),

                TextColumn::make('urutan')
                    ->label('Urutan')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('urutan')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada periode kepengurusan')
            ->emptyStateDescription('Buat periode pertama, tandai sebagai aktif, lalu isi anggotanya.');
    }
}
