<?php

namespace App\Filament\Resources\ColdLeadResource\Pages;

use App\Filament\Resources\ColdLeadResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditColdLead extends EditRecord
{
    protected static string $resource = ColdLeadResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
