<?php

namespace App\Filament\Pages;

use App\Mail\TeamInvitationMail;
use App\Models\TeamInvitation;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InviteTeamMemberPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Invite Member';
    protected static ?string $slug = 'invite-member';
    protected static string $view = 'filament.pages.invite-member';
    protected static ?int $navigationSort = 15;

    public string $email = '';
    public string $role  = 'cold_caller';

    public array $pendingInvitations = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && in_array($user->role, ['super_admin', 'owner', 'admin'], true);
    }

    public function mount(): void
    {
        $teamId = auth()->user()?->team_id;
        $this->pendingInvitations = TeamInvitation::where('team_id', $teamId)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->with('invitedBy')
            ->latest()
            ->get()
            ->toArray();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send_invite')
                ->label('Send Invitation')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->form([
                    TextInput::make('email')
                        ->label('Email Address')
                        ->email()
                        ->required(),
                    Select::make('role')
                        ->options([
                            'admin'               => 'Admin',
                            'acquisition_manager' => 'Acquisition Manager',
                            'lead_manager'        => 'Lead Manager',
                            'cold_caller'         => 'Cold Caller',
                            'dispo_manager'       => 'Dispo Manager',
                        ])
                        ->default('cold_caller')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $user   = auth()->user();
                    $teamId = $user?->team_id;

                    if (!$teamId) {
                        Notification::make()->title('No team found')->danger()->send();
                        return;
                    }

                    // Revoke any existing pending invite for this email+team
                    TeamInvitation::where('team_id', $teamId)
                        ->where('email', $data['email'])
                        ->whereNull('accepted_at')
                        ->delete();

                    $invitation = TeamInvitation::create([
                        'team_id'    => $teamId,
                        'invited_by' => $user->id,
                        'email'      => $data['email'],
                        'role'       => $data['role'],
                        'token'      => Str::random(64),
                        'expires_at' => now()->addDays(7),
                    ]);

                    try {
                        Mail::to($data['email'])->send(new TeamInvitationMail($invitation));
                        Notification::make()->title('Invitation sent to ' . $data['email'])->success()->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Invitation created but email failed: ' . $e->getMessage())
                            ->warning()
                            ->send();
                    }

                    $this->mount(); // reload pending list
                }),
        ];
    }
}
