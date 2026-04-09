<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['team_id'] = $data['team_id'] ?? auth()->user()?->team_id;
        return $data;
    }
}
