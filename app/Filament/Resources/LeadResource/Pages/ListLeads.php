<?php

namespace App\Filament\Resources\LeadResource\Pages;

use App\Filament\Resources\LeadResource;
use App\Models\Lead;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListLeads extends ListRecords
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('import_csv')
                ->label('Import CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->form([
                    FileUpload::make('csv')
                        ->label('CSV File')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'])
                        ->disk('local')
                        ->directory('imports')
                        ->maxSize(5120)
                        ->required()
                        ->helperText('Columns supported: first_name, last_name, owner_name, phone, email, lead_source, notes, motivation_level, asking_price, stage'),
                ])
                ->action(function (array $data) {
                    $path = is_array($data['csv']) ? array_values($data['csv'])[0] : $data['csv'];
                    $fullPath = Storage::disk('local')->path($path);

                    if (!file_exists($fullPath)) {
                        Notification::make()->title('File not found')->danger()->send();
                        return;
                    }

                    $teamId = auth()->user()?->team_id;
                    $rows = array_map('str_getcsv', file($fullPath));
                    $header = array_map(fn ($h) => strtolower(trim($h)), array_shift($rows) ?? []);

                    $imported = 0;
                    $skipped = 0;

                    foreach ($rows as $row) {
                        if (count(array_filter($row)) === 0) {
                            continue;
                        }
                        $data = array_combine($header, array_pad($row, count($header), null));

                        $ownerName = $data['owner_name']
                            ?? trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''))
                            ?: null;
                        $phone = $data['phone'] ?? null;
                        $email = $data['email'] ?? null;

                        if (!$ownerName && !$phone && !$email) {
                            $skipped++;
                            continue;
                        }

                        if ($phone && Lead::where('team_id', $teamId)->where('phone', $phone)->exists()) {
                            $skipped++;
                            continue;
                        }

                        Lead::create([
                            'team_id'          => $teamId,
                            'owner_name'       => $ownerName,
                            'phone'            => $phone,
                            'email'            => $email,
                            'lead_source'      => $data['lead_source'] ?? 'Import',
                            'notes'            => $data['notes'] ?? null,
                            'motivation_level' => is_numeric($data['motivation_level'] ?? null) ? (int) $data['motivation_level'] : null,
                            'asking_price'     => is_numeric($data['asking_price'] ?? null) ? (float) $data['asking_price'] : null,
                            'stage'            => $data['stage'] ?? 'new_lead',
                        ]);
                        $imported++;
                    }

                    @unlink($fullPath);

                    Notification::make()
                        ->title("Imported {$imported} lead(s), skipped {$skipped}")
                        ->success()
                        ->send();
                }),
        ];
    }
}
