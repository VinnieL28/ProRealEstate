<?php

namespace App\Filament\Resources\CallLogResource\Pages;

use App\Filament\Resources\CallLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCallLog extends CreateRecord
{
    protected static string $resource = CallLogResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['team_id'] = $data['team_id'] ?? auth()->user()?->team_id;
        return $data;
    }
}
