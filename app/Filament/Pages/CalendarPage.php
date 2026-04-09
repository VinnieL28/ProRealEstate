<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CalendarWidget;
use Filament\Pages\Page;

class CalendarPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Productivity';
    protected static ?string $navigationLabel = 'Calendar';
    protected static ?string $slug = 'calendar';
    protected static string $view = 'filament.pages.calendar';
    protected static ?int $navigationSort = 5;

    public function getWidgets(): array
    {
        return [
            CalendarWidget::class,
        ];
    }
}
