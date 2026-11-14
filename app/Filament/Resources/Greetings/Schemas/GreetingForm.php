<?php

namespace App\Filament\Resources\Greetings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GreetingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama')
                    ->hint('Contoh: KH. Muhammad Fianto')
                    ->required()
                    ->maxLength(100),

                TextInput::make('role')
                    ->label('Jabatan')
                    ->hint('Contoh: Pembina Asrama')
                    ->required()
                    ->maxLength(100),

                Textarea::make('body')
                    ->label('Isi sambutan')
                    ->required()
                    ->rows(10)
                    ->helperText('Pisahkan setiap paragraf dengan satu baris kosong.')
                    ->columnSpanFull(),

                FileUpload::make('photo')
                    ->label('Foto (opsional)')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(3072)
                    ->storeFiles(false)
                    ->helperText('Foto maksimal 3 MB dan disimpan di database situs agar tahan deploymen. Biarkan kosong bila tidak memakai foto.')
                    ->columnSpanFull(),

                Toggle::make('is_active')
                    ->label('Tampilkan di halaman Profil')
                    ->helperText('Hanya satu sambutan yang tampil; mengaktifkan ini otomatis menonaktifkan sambutan lain. Tanpa sambutan aktif, section Sambutan tersembunyi dari publik.')
                    ->default(false),
            ]);
    }
}
