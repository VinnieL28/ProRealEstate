<?php

namespace App\Filament\Pages;

use App\Models\Attachment;
use App\Models\Lead;
use App\Models\Deal;
use App\Models\Property;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class FileManagerPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';
    protected static ?string $navigationGroup = 'Productivity';
    protected static string $view = 'filament.pages.file-manager';

    public array $attachments = [];

    public array $data = [];

    public function mount(): void
    {
        $this->form->fill();
        $this->loadAttachments();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->label('File Name')->required(),
                Select::make('related_type')->options([
                    'Lead' => 'Lead',
                    'Deal' => 'Deal',
                    'Property' => 'Property',
                ])->required()->reactive(),
                Select::make('related_id')
                    ->label('Related Record')
                    ->options(function (callable $get) {
                        return match ($get('related_type')) {
                            'Lead' => Lead::orderBy('owner_name')->whereNotNull('owner_name')->pluck('owner_name', 'id')->toArray(),
                            'Deal' => Deal::orderBy('name')->whereNotNull('name')->pluck('name', 'id')->toArray(),
                            'Property' => Property::orderBy('address')->whereNotNull('address')->pluck('address', 'id')->toArray(),
                            default => [],
                        };
                    })
                    ->searchable()
                    ->required(),
                FileUpload::make('file')
                    ->disk(config('filesystems.default'))
                    ->directory('attachments')
                    ->maxSize(10240)
                    ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/plain', 'text/csv'])
                    ->required(),
            ])
            ->statePath('data');
    }

    public function upload(): void
    {
        $data = $this->form->getState();
        $path = is_array($data['file']) ? array_values($data['file'])[0] : $data['file'];

        Attachment::create([
            'team_id'      => auth()->user()?->team_id,
            'related_type' => $data['related_type'],
            'related_id'   => $data['related_id'],
            'name'         => $data['name'],
            'path'         => $path,
            'mime_type'    => Storage::mimeType($path),
            'size'         => Storage::size($path),
            'uploaded_by'  => auth()->id(),
        ]);

        Notification::make()->title('File uploaded')->success()->send();
        $this->form->fill();
        $this->loadAttachments();
    }

    public function loadAttachments(): void
    {
        $teamId = auth()->user()?->team_id;
        $this->attachments = Attachment::query()
            ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
            ->latest()
            ->limit(50)
            ->get()
            ->toArray();
    }
}
