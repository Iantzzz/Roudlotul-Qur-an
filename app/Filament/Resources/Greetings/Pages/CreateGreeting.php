<?php

namespace App\Filament\Resources\Greetings\Pages;

use App\Filament\Resources\Concerns\ManagesSiteMediaUpload;
use App\Filament\Resources\Greetings\GreetingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGreeting extends CreateRecord
{
    use ManagesSiteMediaUpload;

    protected static string $resource = GreetingResource::class;
}
