<?php

namespace App\Filament\Resources\SellerLeadResource\Pages;

use App\Filament\Resources\SellerLeadResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSellerLeads extends ListRecords
{
    protected static string $resource = SellerLeadResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
