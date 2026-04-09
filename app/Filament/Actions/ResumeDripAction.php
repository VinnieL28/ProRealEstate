<?php

namespace App\Filament\Actions;

use App\Jobs\ResumeDripJob;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;

class ResumeDripAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Resume Drip')
            ->form([
                Textarea::make('notes')
                    ->label('Restart Notes')
                    ->rows(3),
            ])
            ->action(function ($record): void {
                ResumeDripJob::dispatch(get_class($record), $record->getKey());

                Notification::make()
                    ->title('Drip restart queued')
                    ->success()
                    ->send();
            });
    }
}
