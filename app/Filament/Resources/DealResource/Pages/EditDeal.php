<?php

namespace App\Filament\Resources\DealResource\Pages;

use App\Filament\Resources\DealResource;
use Filament\Resources\Pages\EditRecord;

class EditDeal extends EditRecord
{
    protected static string $resource = DealResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['team_id'] = $data['team_id'] ?? $this->record->team_id ?? auth()->user()?->team_id;
        return $data;
    }
}
