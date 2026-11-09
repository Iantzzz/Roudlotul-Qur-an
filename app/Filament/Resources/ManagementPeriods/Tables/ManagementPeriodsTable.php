<?php

namespace App\Filament\Resources\ManagementPeriods\Tables;

use App\Models\ManagementPeriod;
use App\Support\ManagementCloner;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use App\Filament\Resources\ManagementPeriods\Pages\ListManagementPeriods;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class ManagementPeriodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->label('Periode')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('units_count')
                    ->counts('units')
                    ->label('Jumlah unit')
                    ->alignCenter(),

                TextColumn::make('members_count')
                    ->counts('members')
                    ->label('Jumlah anggota')
                    ->formatStateUsing(fn ($state): string => (string) $state)
                    ->alignCenter(),

                ToggleColumn::make('is_active')
                    ->label('Aktif'),

                TextColumn::make('urutan')
                    ->label('Urutan')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('urutan')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),

                Action::make('cloneStruktur')
                    ->label('Salin struktur…')
                    ->color('gray')
                    ->modalHeading('Salin struktur dari periode ini')
                    ->modalDescription('Hanya unit, nama, hubungan parent-child, dan urutan yang disalin — TIDAK ada anggota yang disalin. Periode sumber tidak diubah.')
                    ->form([
                        Select::make('target_period_id')
                            ->label('Periode tujuan')
                            ->options(function (ManagementPeriod $record): array {
                                return ManagementPeriod::query()
                                    ->whereKeyNot($record->getKey())
                                    ->orderBy('urutan')
                                    ->orderBy('id')
                                    ->get()
                                    ->mapWithKeys(fn (ManagementPeriod $period): array => [
                                        (string) $period->getKey() => $period->label,
                                    ])
                                    ->all();
                            })
                            ->searchable()
                            ->required()
                            ->helperText('Tujuan harus periode lain yang belum memiliki struktur unit.'),
                    ])
                    ->action(function (array $data, ManagementPeriod $record, Action $action): void {
                        $target = ManagementPeriod::query()->find((int) ($data['target_period_id'] ?? 0));

                        if ($target === null) {
                            throw ValidationException::withMessages([
                                'target_period_id' => 'Periode tujuan tidak ditemukan.',
                            ]);
                        }

                        try {
                            $count = ManagementCloner::run($record, $target);

                            Notification::make()
                                ->title("Struktur disalin: {$count} unit diteruskan ke periode {$target->label} (tanpa anggota).")
                                ->success()
                                ->send();
                        } catch (\RuntimeException $e) {
                            throw ValidationException::withMessages([
                                'target_period_id' => $e->getMessage(),
                            ]);
                        }
                    }),

                DeleteAction::make()
                    ->modalDescription(function (ManagementPeriod $record): string {
                        return sprintf(
                            'Periode "%s" memuat %d unit struktur dan %d anggota. Menghapus periode akan menghapus SEMUANYA secara permanen (unit dan anggota periode ini saja, periode lain tidak tersentuh).',
                            $record->label,
                            $record->units()->count(),
                            $record->members()->count(),
                        );
                    })
                    ->modalHeading('Hapus periode kepengurusan')
                    ->successRedirectUrl(ListManagementPeriods::getUrl()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalDescription(function (ListManagementPeriods $livewire): string {
                            $records = $livewire->getSelectedTableRecords();

                            $units = $records->sum(fn (ManagementPeriod $period): int => $period->units()->count());
                            $members = $records->sum(fn (ManagementPeriod $period): int => $period->members()->count());

                            return sprintf(
                                '%d periode terpilih — total %d unit struktur dan %d anggota akan TERHAPUS permanen. Data tidak dapat dipulihkan tanpa backup.',
                                $records->count(),
                                $units,
                                $members,
                            );
                        }),
                ]),
            ])
            ->emptyStateHeading('Belum ada periode kepengurusan')
            ->emptyStateDescription('Buat periode pertama, tandai sebagai aktif, lalu isi anggotanya.');
    }
}
