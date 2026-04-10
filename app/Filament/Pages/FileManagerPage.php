<?php

namespace App\Filament\Pages;

use App\Models\Attachment;
use App\Models\Lead;
use App\Models\Deal;
use App\Models\Property;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class FileManagerPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-folder-open';
    protected static ?string $navigationGroup = 'Productivity';
    protected static string $view = 'filament.pages.file-manager';

    public array $attachments;

    public ?string $related_type = null;
    public ?int $related_id = null;
    public ?string $name = null;
    public mixed $file = null;

    public function mount(): void
    {
        $this->loadAttachments();
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('name')->label('File Name')->required(),
            Select::make('related_type')->options([
                'Lead' => 'Lead',
                'Deal' => 'Deal',
                'Property' => 'Property',
            ])->required(),
            Select::make('related_id')
                ->label('Related Record')
                ->options(function (callable $get) {
                    return match ($get('related_type')) {
                        'Lead' => Lead::orderBy('owner_name')->pluck('owner_name', 'id'),
                        'Deal' => Deal::orderBy('name')->pluck('name', 'id'),
                        'Property' => Property::orderBy('address')->pluck('address', 'id'),
                        default => [],
                    };
                })
                ->searchable()
                ->required(),
            FileUpload::make('file')
                ->disk(config('filesystems.default'))
                ->directory('attachments')
                ->required(),
        ];
    }

    public function upload(): void
    {
        $data = $this->form->getState();
        $path = $data['file'];

        Attachment::create([
            'team_id' => auth()->user()?->team_id,
            'related_type' => $data['related_type'],
            'related_id' => $data['related_id'],
            'name' => $data['name'],
            'path' => $path,
            'mime_type' => Storage::mimeType($path),
            'size' => Storage::size($path),
            'uploaded_by' => auth()->id(),
        ]);

        Notification::make()->title('File uploaded')->success()->send();
        $this->reset(['related_type', 'related_id', 'name', 'file']);
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

    protected function getFormModel(): string
    {
        return Attachment::class;
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->getFormSchema());
    }
}
