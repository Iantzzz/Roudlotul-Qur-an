<?php

namespace App\Filament\Resources\ManagementPeriods\RelationManagers;

use App\Models\ManagementUnit;
use App\Support\ManagementStructure;
use App\Support\ManagementTree;
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
 * Kelola pohon unit (kelompok/bidang) satu periode:
 * tambah/edit/hapus, tentukan hubungan parent, dan urutan.
 * Kandidat Unit induk dibatasi pada unit periode yang sama dan
 * dikecualikan dari diri/keturunannya (anti-siklus); validasi
 * kedalaman menerapkan ManagementStructure::MAX_DEPTH.
 */
class UnitsRelationManager extends RelationManager
{
    protected static string $relationship = 'units';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
                    ->label('Nama unit')
                    ->hint('Contoh: Bidang Kepengurusan, Kedisiplinan')
                    ->required()
                    ->maxLength(100)
                    ->helperText('Nama bebas untuk kelompok, bidang, maupun jabatan.'),

                Select::make('parent_id')
                    ->label('Unit induk')
                    ->options(function (RelationManager $livewire, ?ManagementUnit $record): array {
                        $periodId = (int) $livewire->ownerRecord->getKey();

                        $candidates = ManagementUnit::query()
                            ->where('management_period_id', $periodId)
                            ->orderBy('urutan')
                            ->orderBy('nama')
                            ->get();

                        $excluded = [];

                        if ($record !== null) {
                            $excluded[] = (int) $record->getKey();

                            foreach ($candidates as $candidate) {
                                // Keturunan unit ini tidak boleh menjadi induknya.
                                $chain = $candidate;

                                while ($chain !== null && (int) $chain->getKey() !== (int) $record->getKey()) {
                                    $chain = $chain->parent_id !== null
                                        ? ManagementUnit::query()->find($chain->parent_id)
                                        : null;
                                }

                                if ($chain !== null) {
                                    $excluded[] = (int) $candidate->getKey();
                                }
                            }
                        }

                        return $candidates
                            ->reject(fn (ManagementUnit $candidate): bool => in_array((int) $candidate->getKey(), $excluded, true))
                            ->mapWithKeys(fn (ManagementUnit $candidate): array => [
                                (string) $candidate->getKey() => $candidate->nama,
                            ])
                            ->all();
                    })
                    ->searchable()
                    ->native(false)
                    ->hint(ManagementStructure::MAX_DEPTH.' tingkat maksimum')
                    ->helperText('Biarkan kosong untuk menjadikan unit ini wadah tingkat akar.'),

                TextInput::make('urutan')
                    ->label('Urutan tampil')
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->maxValue(99999)
                    ->default(0)
                    ->helperText('Unit berturutan kecil tampil lebih dulu.'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama')
            ->columns([
                TextColumn::make('urutan')
                    ->label('Urutan')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('nama')
                    ->label('Unit')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('parent.nama')
                    ->label('Unit induk')
                    ->placeholder('—'),

                TextColumn::make('members_count')
                    ->counts('members')
                    ->label('Anggota')
                    ->alignCenter(),
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

                DeleteAction::make()
                    ->modalDescription(function (ManagementUnit $record): string {
                        $impactMembers = $record->members()->count();
                        $impactChildren = ManagementTree::descendantCount($record);

                        return sprintf(
                            '%d anggota akan kehilangan wadah dan tetap tampil pada wadah "Anggota Lain". %d unit anak akan naik menjadi unit tingkat akar. Hierarki pada cabang ini menipis — data anggota TIDAK terhapus.',
                            $impactMembers,
                            $impactChildren,
                        );
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalDescription(function (RelationManager $livewire): string {
                            $records = $livewire->getSelectedTableRecords();

                            $impactMembers = $records->sum(fn (ManagementUnit $unit): int => $unit->members()->count());
                            $impactChildren = $records->sum(fn (ManagementUnit $unit): int => ManagementTree::descendantCount($unit));

                            return sprintf(
                                '%d unit terpilih — total %d anggota akan kehilangan wadah (tetap tampil pada wadah "Anggota Lain") dan %d unit anak akan naik tingkat. Data anggota tidak terhapus.',
                                $records->count(),
                                $impactMembers,
                                $impactChildren,
                            );
                        }),
                ]),
            ])
            ->emptyStateHeading('Belum ada unit struktur pada periode ini')
            ->emptyStateDescription('Tambahkan wadah pertama (mis. "Pimpinan & Pembina" atau nama bidang); halaman Profil akan merender pohon dari data ini.');
    }
}
