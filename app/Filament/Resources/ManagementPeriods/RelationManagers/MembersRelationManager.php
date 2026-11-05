<?php

namespace App\Filament\Resources\ManagementPeriods\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Kelola anggota satu periode langsung di dalam halaman periode.
 * Anggota tidak pernah dipakai ulang antarperiode, sehingga yang
 * diperlukan hanya Create/Edit/Delete tanpa asosiasi silang.
 */
class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('jabatan')
                    ->label('Jabatan')
                    ->hint('Contoh: Pembina')
                    ->required()
                    ->maxLength(80),

                TextInput::make('nama')
                    ->required()
                    ->maxLength(120),

                TextInput::make('urutan')
                    ->label('Urutan tampil')
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->maxValue(99999)
                    ->default(0)
                    ->helperText('Angka kecil tampil lebih dulu di halaman Profil.'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('jabatan')
            ->columns([
                TextColumn::make('urutan')
                    ->label('Urutan')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('jabatan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),
            ])
            ->defaultSort('urutan')
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada anggota pada periode ini')
            ->emptyStateDescription('Tambahkan anggota berdasarkan urutan tampil di halaman Profil.');
    }
}
