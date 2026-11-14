<?php

namespace App\Filament\Resources\GalleryItems\Schemas;

use App\Models\GalleryItem;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GalleryItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('caption')
                    ->label('Keterangan foto')
                    ->hint('Contoh: Apel pagi di koridor asrama')
                    ->required()
                    ->maxLength(120),

                FileUpload::make('photo')
                    ->label('Ganti foto (unggahan)')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(3072)
                    ->storeFiles(false)
                    ->helperText('Foto maksimal 3 MB, disimpan di database situs. Biarkan kosong untuk tetap memakai foto saat ini.')
                    ->columnSpanFull(),

                Checkbox::make('clear_photo')
                    ->label('Kosongkan foto (kembali memakai foto bawaan situs)')
                    ->helperText('Berlaku saat disimpan; menghapus foto unggahan milik foto galeri ini.'),

                TextInput::make('sort')
                    ->label('Urutan')
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->maxValue(99999)
                    ->default(fn (): int => (int) GalleryItem::query()->max('sort') + 1)
                    ->helperText('Angka kecil tampil lebih dulu — mengatur urutan foto di galeri.'),
            ]);
    }
}
