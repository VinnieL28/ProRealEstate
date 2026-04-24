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
                ->url(fn () => route('backup.full-zip'))
                ->openUrlInNewTab(),
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
