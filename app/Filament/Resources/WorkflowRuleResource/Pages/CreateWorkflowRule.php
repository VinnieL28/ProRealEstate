<?php

namespace App\Filament\Resources\WorkflowRuleResource\Pages;

use App\Filament\Resources\WorkflowRuleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkflowRule extends CreateRecord
{
    protected static string $resource = WorkflowRuleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['team_id'] = auth()->user()?->team_id;
        return $data;
    }
}
