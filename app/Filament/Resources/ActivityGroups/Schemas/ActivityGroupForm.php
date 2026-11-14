<?php

namespace App\Filament\Resources\ActivityGroups\Schemas;

use App\Models\ActivityGroup;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ActivityGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Judul kelompok')
                    ->hint('Contoh: Ibadah & Tahfizh')
                    ->required()
                    ->maxLength(80),

                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->columnSpanFull(),

                TextInput::make('sort')
                    ->label('Urutan')
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->maxValue(999)
                    ->default(fn (): int => (int) ActivityGroup::query()->max('sort') + 1),
            ]);
    }
}
