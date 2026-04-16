<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class AiAssistant extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-sparkles';
    protected static ?string $navigationLabel = 'AI Assistant';
    protected static ?string $slug            = 'ai-assistant';
    protected static string  $view            = 'filament.pages.ai-assistant';
    protected static ?int    $navigationSort  = 99;
}
