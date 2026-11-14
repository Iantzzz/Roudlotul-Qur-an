<?php

namespace App\Filament\Resources\Activities\Schemas;

use App\Models\Activity;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ActivityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('activity_group_id')
                    ->label('Kelompok kegiatan')
                    ->relationship('group', 'title')
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->placeholder('— Tanpa kelompok —')
                    ->helperText('Kegiatan tanpa kelompok tidak tampil di halaman Kegiatan; gunakan untuk kartu beranda.'),

                TextInput::make('label')
                    ->label('Keterangan kecil')
                    ->hint('Contoh: Kegiatan harian')
                    ->required()
                    ->maxLength(60),

                TextInput::make('title')
                    ->label('Judul kegiatan')
                    ->required()
                    ->maxLength(100),

                Textarea::make('text')
                    ->label('Deskripsi')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),

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
                    ->helperText('Berlaku saat disimpan; menghapus foto unggahan milik kegiatan ini.'),

                Toggle::make('is_featured')
                    ->label('Tampilkan di beranda')
                    ->helperText('Kegiatan bertanda ini tampil sebagai kartu pada bagian kegiatan beranda.'),

                TextInput::make('sort')
                    ->label('Urutan')
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->maxValue(9999)
                    ->default(fn (): int => (int) Activity::query()->max('sort') + 1),
            ]);
    }
}
