<?php

namespace App\Filament\Resources\SmsLogResource\Pages;

use App\Filament\Resources\SmsLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSmsLog extends CreateRecord
{
    protected static string $resource = SmsLogResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['team_id'] = $data['team_id'] ?? auth()->user()?->team_id;
        return $data;
    }
}
