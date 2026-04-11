<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Models\Team;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class OnboardingPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Onboarding';
    protected static ?string $slug = 'onboarding';
    protected static string $view = 'filament.pages.onboarding';
    protected static ?int $navigationSort = 1;

    public int $currentStep = 1;
    public int $percent = 0;

    // Step 1 — Company Info
    public string $company_name  = '';
    public string $timezone      = 'America/New_York';
    public string $currency      = 'USD';

    // Step 2 — Invite Members (handled by InviteTeamMemberPage)
    // Step 3 — Twilio
    public string $twilio_sid    = '';
    public string $twilio_token  = '';
    public string $twilio_phone  = '';
    // Step 4 — Gmail
    public string $gmail_client_id     = '';
    public string $gmail_client_secret = '';

    public ?Team $team = null;
    public array $steps = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && in_array($user->role, ['super_admin', 'owner', 'admin'], true);
    }

    public function mount(): void
    {
        $user       = auth()->user();
        $this->team = Team::find($user?->team_id);

        $this->steps   = $this->team?->onboarding_steps ?? [];
        $this->percent = $this->team?->onboardingPercent() ?? 0;

        $setting = Setting::where('team_id', $user?->team_id)->first();

        // Pre-fill from existing settings
        $this->company_name       = $this->team?->name ?? '';
        $this->timezone           = $setting?->timezone ?? 'America/New_York';
        $this->currency           = $this->team?->default_currency ?? 'USD';
        $this->twilio_sid         = $setting?->twilio_sid ?? '';
        $this->twilio_token       = $setting?->twilio_token ?? '';
        $this->twilio_phone       = $setting?->twilio_phone_number ?? '';
        $this->gmail_client_id    = $setting?->gmail_client_id ?? '';
        $this->gmail_client_secret= $setting?->gmail_client_secret ?? '';

        // Auto-detect current step based on what's done
        $done = array_keys(array_filter($this->steps));
        $this->currentStep = empty($done) ? 1 : (max($done) + 1 <= 5 ? max($done) + 1 : 6);
    }

    public function saveStep1(): void
    {
        $this->validate([
            'company_name' => 'required|string|max:255',
            'timezone'     => 'required|string',
            'currency'     => 'required|string|max:3',
        ]);

        if ($this->team) {
            $this->team->update([
                'name'             => $this->company_name,
                'default_currency' => $this->currency,
            ]);

            $setting = Setting::firstOrCreate(['team_id' => $this->team->id]);
            $setting->update(['timezone' => $this->timezone, 'company_name' => $this->company_name]);

            $this->team->markOnboardingStep(1);
        }

        Notification::make()->title('Company info saved')->success()->send();
        $this->currentStep = 2;
        $this->percent = $this->team?->onboardingPercent() ?? 0;
    }

    public function skipStep(int $step): void
    {
        if ($this->team) {
            $this->team->markOnboardingStep($step);
        }
        $this->currentStep = $step + 1;
        $this->percent = $this->team?->onboardingPercent() ?? 0;
    }

    public function saveTwilio(): void
    {
        if ($this->team && ($this->twilio_sid || $this->twilio_token || $this->twilio_phone)) {
            $setting = Setting::firstOrCreate(['team_id' => $this->team->id]);
            $setting->update([
                'twilio_sid'          => $this->twilio_sid,
                'twilio_token'        => $this->twilio_token,
                'twilio_phone_number' => $this->twilio_phone,
            ]);
            $this->team->markOnboardingStep(3);
        }
        $this->currentStep = 4;
        $this->percent = $this->team?->onboardingPercent() ?? 0;
        Notification::make()->title('Twilio credentials saved')->success()->send();
    }

    public function saveGmail(): void
    {
        if ($this->team && ($this->gmail_client_id || $this->gmail_client_secret)) {
            $setting = Setting::firstOrCreate(['team_id' => $this->team->id]);
            $setting->update([
                'gmail_client_id'     => $this->gmail_client_id,
                'gmail_client_secret' => $this->gmail_client_secret,
            ]);
            $this->team->markOnboardingStep(4);
        }
        $this->currentStep = 5;
        $this->percent = $this->team?->onboardingPercent() ?? 0;
        Notification::make()->title('Gmail credentials saved')->success()->send();
    }

    public function finishOnboarding(): void
    {
        if ($this->team) {
            $this->team->markOnboardingStep(5);
        }
        $this->currentStep = 6;
        $this->percent = 100;
        Notification::make()->title('Onboarding complete! Welcome to Pro Real Estate CRM.')->success()->send();
    }
}
