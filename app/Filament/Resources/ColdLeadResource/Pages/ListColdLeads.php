<?php

namespace App\Filament\Resources\ColdLeadResource\Pages;

use App\Filament\Resources\ColdLeadResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListColdLeads extends ListRecords
{
    protected static string $resource = ColdLeadResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
