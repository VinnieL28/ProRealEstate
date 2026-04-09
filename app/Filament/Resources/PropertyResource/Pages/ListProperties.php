<?php

namespace App\Filament\Resources\PropertyResource\Pages;

use App\Filament\Resources\PropertyResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListProperties extends ListRecords
{
    protected static string $resource = PropertyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
