<?php

namespace App\Filament\Resources\ManagementPeriods\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ManagementPeriodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('label')
                    ->label('Nama periode')
                    ->hint('Contoh: 2025/2026')
                    ->required()
                    ->maxLength(60),

                Toggle::make('is_active')
                    ->label('Periode aktif')
                    ->helperText('Hanya periode aktif yang tampil di halaman Profil; menandai periode ini aktif otomatis menonaktifkan periode lain.')
                    ->default(false)
                    ->live(),

                TextInput::make('urutan')
                    ->label('Urutan')
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->maxValue(99999)
                    ->default(0)
                    ->helperText('Periode dengan urutan lebih kecil tampil lebih atas di daftar admin.'),
            ]);
    }
}
