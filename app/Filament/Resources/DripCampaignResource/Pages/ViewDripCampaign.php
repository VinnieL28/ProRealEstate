<?php

namespace App\Filament\Resources\DripCampaignResource\Pages;

use App\Filament\Resources\DripCampaignResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDripCampaign extends ViewRecord
{
    protected static string $resource = DripCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\EditAction::make()];
    }
}
