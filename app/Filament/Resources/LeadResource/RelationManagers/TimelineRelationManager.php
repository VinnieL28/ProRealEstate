<?php

namespace App\Filament\Resources\LeadResource\RelationManagers;

use App\Models\Activity;
use App\Models\CallLog;
use App\Models\EmailLog;
use App\Models\SmsLog;
use App\Models\Task;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class TimelineRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    protected static ?string $title = 'Activity Timeline';

    protected static ?string $icon = 'heroicon-o-clock';

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('type')->default('note')->required(),
            Textarea::make('description')->rows(3)->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        // We build a merged timeline in getViewData() and render via custom blade
        return $table
            ->query(fn () => Activity::query()->where('related_type', 'Lead')->where('related_id', $this->ownerRecord->id)->latest())
            ->columns([
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('description')->limit(80)->wrap(),
                Tables\Columns\TextColumn::make('user.name')->label('By'),
                Tables\Columns\TextColumn::make('created_at')->dateTime('M j, Y g:i A')->label('When')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Note')
                    ->icon('heroicon-o-pencil-square')
                    ->mutateFormDataUsing(function (array $data) {
                        $data['team_id']      = $this->ownerRecord->team_id;
                        $data['related_type'] = 'Lead';
                        $data['related_id']   = $this->ownerRecord->id;
                        $data['user_id']      = auth()->id();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->paginated([10, 25, 50]);
    }
}
