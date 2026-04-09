<?php

namespace App\Filament\Resources\DealResource\Pages;

use App\Filament\Resources\DealResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDeal extends CreateRecord
{
    protected static string $resource = DealResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['team_id'] = $data['team_id'] ?? auth()->user()?->team_id;
        return $data;
    }
}
