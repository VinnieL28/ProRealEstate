<?php

namespace App\Filament\Resources\DripCampaignResource\Pages;

use App\Filament\Resources\DripCampaignResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDripCampaign extends CreateRecord
{
    protected static string $resource = DripCampaignResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['team_id'] = auth()->user()?->team_id;
        return $data;
    }
}
