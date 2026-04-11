<?php

namespace App\Filament\Pages;

use App\Models\Team;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SuperAdminTeamsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Super Admin';
    protected static ?string $navigationLabel = 'All Teams';
    protected static ?string $slug = 'super-admin/teams';
    protected static string $view = 'filament.pages.super-admin-teams';
    protected static ?int $navigationSort = 1;

    public array $teams = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public function mount(): void
    {
        $this->loadTeams();
    }

    protected function loadTeams(): void
    {
        $this->teams = Team::withCount(['users', 'leads', 'deals'])
            ->with('owner')
            ->latest()
            ->get()
            ->toArray();
    }

    public function switchToTeam(int $teamId): void
    {
        $team = Team::find($teamId);
        if (!$team) return;

        // Store the team switch in session so super admin can view as that team
        session(['super_admin_viewing_team' => $teamId]);

        Notification::make()
            ->title('Now viewing as team: ' . $team->name)
            ->success()
            ->send();

        $this->loadTeams();
    }

    public function clearTeamSwitch(): void
    {
        session()->forget('super_admin_viewing_team');
        Notification::make()->title('Reverted to super admin view')->success()->send();
        $this->loadTeams();
    }

    public function toggleTeamActive(int $teamId): void
    {
        $team = Team::find($teamId);
        if (!$team) return;

        $team->update(['is_active' => !$team->is_active]);
        Notification::make()
            ->title('Team ' . ($team->is_active ? 'activated' : 'deactivated'))
            ->success()
            ->send();

        $this->loadTeams();
    }
}
