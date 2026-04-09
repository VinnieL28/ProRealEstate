<?php

namespace App\Filament\Resources\SmsLogResource\Pages;

use App\Filament\Resources\SmsLogResource;
use Filament\Resources\Pages\EditRecord;

class EditSmsLog extends EditRecord
{
    protected static string $resource = SmsLogResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['team_id'] = $data['team_id'] ?? $this->record->team_id ?? auth()->user()?->team_id;
        return $data;
    }
}
