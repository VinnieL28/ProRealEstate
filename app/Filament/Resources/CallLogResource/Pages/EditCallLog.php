<?php

namespace App\Filament\Resources\CallLogResource\Pages;

use App\Filament\Resources\CallLogResource;
use Filament\Resources\Pages\EditRecord;

class EditCallLog extends EditRecord
{
    protected static string $resource = CallLogResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['team_id'] = $data['team_id'] ?? $this->record->team_id ?? auth()->user()?->team_id;
        return $data;
    }
}
