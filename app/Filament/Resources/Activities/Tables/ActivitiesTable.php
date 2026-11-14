<?php

namespace App\Filament\Resources\Activities\Tables;

use App\Models\Activity;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Foto')
                    ->state(fn (Activity $record): ?string => $record->image_url)
                    ->width(52)
                    ->height(52),

                TextColumn::make('title')
                    ->label('Kegiatan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('label')
                    ->label('Keterangan')
                    ->searchable(),

                TextColumn::make('group.title')
                    ->label('Kelompok')
                    ->placeholder('—'),

                ToggleColumn::make('is_featured')
                    ->label('Beranda'),

                TextColumn::make('sort')
                    ->label('Urutan')
                    ->sortable(),
            ])
            ->defaultSort('sort')
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->modalHeading('Hapus kegiatan')
                    ->modalDescription('Kegiatan yang dihapus hilang dari halaman Kegiatan atau kartu beranda. Tindakan ini tidak bisa dibatalkan.'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
