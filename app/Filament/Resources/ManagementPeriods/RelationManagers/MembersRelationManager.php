<?php

namespace App\Filament\Resources\ManagementPeriods\RelationManagers;

use App\Models\ManagementUnit;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Kelola anggota satu periode langsung di dalam halaman periode.
 * Anggota tidak pernah dipakai ulang antarperiode. Setiap anggota
 * dapat dipasangkan dengan wadah (unit) dari PERIODE YANG SAMA —
 * kandidat wadah dibatasi pada periode pemilik halaman ini; pelanggaran
 * lintas-periode ditolak lagi oleh hook model dan
 * composite FK (PostgreSQL).
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

                Select::make('management_unit_id')
                    ->label('Wadah (unit)')
                    ->options(function (RelationManager $livewire): array {
                        $periodId = (int) $livewire->ownerRecord->getKey();

                        return ManagementUnit::query()
                            ->where('management_period_id', $periodId)
                            ->orderBy('urutan')
                            ->orderBy('nama')
                            ->get()
                            ->mapWithKeys(fn (ManagementUnit $unit): array => [
                                (string) $unit->getKey() => $unit->nama,
                            ])
                            ->all();
                    })
                    ->searchable()
                    ->native(false)
                    ->nullable()
                    ->helperText('Wadah menentukan posisi anggota di bagan kepengurusan.'),

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

                TextColumn::make('unit.nama')
                    ->label('Wadah')
                    ->placeholder('Anggota Lain'),
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
