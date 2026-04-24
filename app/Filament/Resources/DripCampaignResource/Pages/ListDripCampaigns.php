<?php

namespace App\Filament\Resources\DripCampaignResource\Pages;

use App\Filament\Resources\DripCampaignResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDripCampaigns extends ListRecords
{
    protected static string $resource = DripCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
