<?php

namespace App\Filament\Resources\GalleryItems\Tables;

use App\Models\GalleryItem;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GalleryItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Foto')
                    ->state(fn (GalleryItem $record): ?string => $record->image_url)
                    ->width(72)
                    ->height(48),

                TextColumn::make('caption')
                    ->label('Keterangan')
                    ->limit(46)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('sort')
                    ->label('Urutan')
                    ->sortable(),
            ])
            ->defaultSort('sort')
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->modalHeading('Hapus foto galeri')
                    ->modalDescription('Foto yang dihapus hilang dari halaman Galeri dan strip galeri di beranda. Tindakan ini tidak bisa dibatalkan.'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
