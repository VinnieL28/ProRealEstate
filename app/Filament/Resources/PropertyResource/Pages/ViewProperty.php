<?php

namespace App\Filament\Resources\PropertyResource\Pages;

use App\Filament\Resources\PropertyResource;
use App\Filament\Widgets\TimelineWidget;
use Filament\Resources\Pages\ViewRecord;

class ViewProperty extends ViewRecord
{
    protected static string $resource = PropertyResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            TimelineWidget::class,
        ];
    }
}
