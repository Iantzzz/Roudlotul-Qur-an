<?php

namespace App\Filament\Resources\ActivityGroups\Tables;

use App\Models\ActivityGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivityGroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Kelompok')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('activities_count')
                    ->counts('activities')
                    ->label('Jumlah kegiatan'),

                TextColumn::make('sort')
                    ->label('Urutan')
                    ->sortable(),
            ])
            ->defaultSort('sort')
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->modalHeading('Hapus kelompok kegiatan')
                    ->modalDescription(function (ActivityGroup $record): string {
                        return sprintf(
                            'Menghapus kelompok "%s" juga menghapus %d kegiatan di dalamnya secara permanen. Butir kegiatan lain (kartu beranda) tidak tersentuh.',
                            $record->title,
                            (int) $record->activities()->count(),
                        );
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
