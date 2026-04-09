<?php

namespace App\Filament\Resources\LeadResource\Pages;

use App\Filament\Resources\LeadResource;
use App\Filament\Widgets\TimelineWidget;
use Filament\Resources\Pages\ViewRecord;

class ViewLead extends ViewRecord
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            TimelineWidget::class,
        ];
    }
}
