<?php

namespace App\Filament\Resources\LeadResource\Pages;

use App\Filament\Resources\LeadResource;
use App\Filament\Widgets\LeadTimelineWidget;
use Filament\Resources\Pages\EditRecord;

class EditLead extends EditRecord
{
    protected static string $resource = LeadResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['team_id'] = $data['team_id'] ?? $this->record->team_id ?? auth()->user()?->team_id;
        return $data;
    }

    protected function getFooterWidgets(): array
    {
        return [
            LeadTimelineWidget::make(['leadId' => $this->record->id]),
        ];
    }
}
