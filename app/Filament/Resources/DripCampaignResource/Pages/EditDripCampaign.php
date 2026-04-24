<?php

namespace App\Filament\Resources\DripCampaignResource\Pages;

use App\Filament\Resources\DripCampaignResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDripCampaign extends EditRecord
{
    protected static string $resource = DripCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
