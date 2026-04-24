<?php

namespace App\Filament\Resources\SellerLeadResource\Pages;

use App\Filament\Resources\SellerLeadResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSellerLead extends ViewRecord
{
    protected static string $resource = SellerLeadResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\EditAction::make()];
    }
}
