<?php

namespace App\Filament\Pages;

use App\Exports\DealsExport;
use App\Exports\LeadsExport;
use App\Models\Lead;
use App\Models\Deal;
use App\Models\Task;
use App\Models\Property;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class BackupPage extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-archive-box-arrow-down';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Data Export / Backup';
    protected static ?string $slug            = 'backup';
    protected static string  $view            = 'filament.pages.backup';

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['owner', 'admin'], true);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_leads_xlsx')
                ->label('Export Leads (Excel)')
                ->icon('heroicon-o-users')
                ->color('success')
                ->action(function () {
                    return Excel::download(
                        new LeadsExport(auth()->user()?->team_id),
                        'leads-backup-' . now()->format('Ymd-His') . '.xlsx'
                    );
                }),

            Action::make('export_deals_xlsx')
                ->label('Export Deals (Excel)')
                ->icon('heroicon-o-briefcase')
                ->color('info')
                ->action(function () {
                    return Excel::download(
                        new DealsExport(auth()->user()?->team_id),
                        'deals-backup-' . now()->format('Ymd-His') . '.xlsx'
                    );
                }),

            Action::make('export_full_zip')
                ->label('Export Full ZIP (CSV)')
                ->icon('heroicon-o-archive-box-arrow-down')
                ->color('warning')
                ->requiresConfirmation()
                ->modalDescription('This will generate a ZIP of all your data tables as CSV files. It may take a moment for large datasets.')
                ->action(function () {
                    $teamId = auth()->user()?->team_id;
                    $tmpDir = sys_get_temp_dir() . '/crm-backup-' . now()->format('Ymd-His');
                    mkdir($tmpDir, 0755, true);

                    // Export each table as CSV
                    $exports = [
                        'leads'      => Lead::when($teamId, fn ($q) => $q->where('team_id', $teamId))->with('assignedTo')->get(),
                        'deals'      => Deal::when($teamId, fn ($q) => $q->where('team_id', $teamId))->get(),
                        'tasks'      => Task::when($teamId, fn ($q) => $q->where('team_id', $teamId))->get(),
                        'properties' => Property::when($teamId, fn ($q) => $q->where('team_id', $teamId))->get(),
                    ];

                    foreach ($exports as $name => $records) {
                        $file = fopen("{$tmpDir}/{$name}.csv", 'w');
                        if ($records->isNotEmpty()) {
                            fputcsv($file, array_keys($records->first()->toArray()));
                            foreach ($records as $row) {
                                fputcsv($file, array_values($row->toArray()));
                            }
                        }
                        fclose($file);
                    }

                    $zipPath = sys_get_temp_dir() . '/crm-backup-' . now()->format('Ymd-His') . '.zip';
                    $zip = new \ZipArchive();
                    $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
                    foreach (glob("{$tmpDir}/*.csv") as $csv) {
                        $zip->addFile($csv, basename($csv));
                    }
                    $zip->close();

                    return response()->download($zipPath, 'crm-backup-' . now()->format('Ymd') . '.zip')
                        ->deleteFileAfterSend(true);
                }),
        ];
    }

    public function getStats(): array
    {
        $teamId = auth()->user()?->team_id;
        return [
            'leads'      => Lead::when($teamId, fn ($q) => $q->where('team_id', $teamId))->count(),
            'deals'      => Deal::when($teamId, fn ($q) => $q->where('team_id', $teamId))->count(),
            'tasks'      => Task::when($teamId, fn ($q) => $q->where('team_id', $teamId))->count(),
            'properties' => Property::when($teamId, fn ($q) => $q->where('team_id', $teamId))->count(),
        ];
    }
}
