<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSetting extends CreateRecord
{
    protected static string $resource = SettingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['team_id'] = $data['team_id'] ?? auth()->user()?->team_id;
        return $data;
    }
}
