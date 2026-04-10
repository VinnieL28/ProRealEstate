<?php

namespace App\Filament\Resources\WorkflowRuleResource\Pages;

use App\Filament\Resources\WorkflowRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkflowRules extends ListRecords
{
    protected static string $resource = WorkflowRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
