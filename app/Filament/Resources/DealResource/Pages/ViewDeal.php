<?php

namespace App\Filament\Resources\DealResource\Pages;

use App\Filament\Resources\DealResource;
use App\Filament\Widgets\TimelineWidget;
use Filament\Resources\Pages\ViewRecord;

class ViewDeal extends ViewRecord
{
    protected static string $resource = DealResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            TimelineWidget::class,
        ];
    }
}
