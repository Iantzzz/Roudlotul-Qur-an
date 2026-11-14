<?php

namespace App\Filament\Resources\Greetings\Pages;

use App\Filament\Resources\Concerns\ManagesSiteMediaUpload;
use App\Filament\Resources\Greetings\GreetingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGreeting extends EditRecord
{
    use ManagesSiteMediaUpload;

    protected static string $resource = GreetingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalHeading('Hapus sambutan')
                ->modalDescription('Sambutan yang dihapus tidak tampil lagi di halaman Profil. Tindakan ini tidak bisa dibatalkan.'),
        ];
    }
}
