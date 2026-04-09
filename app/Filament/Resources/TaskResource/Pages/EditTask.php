<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use Filament\Resources\Pages\EditRecord;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['team_id'] = $data['team_id'] ?? $this->record->team_id ?? auth()->user()?->team_id;
        return $data;
    }
}
