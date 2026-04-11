<?php

namespace App\Filament\Widgets;

use App\Models\Team;
use Filament\Widgets\Widget;

class OnboardingProgressWidget extends Widget
{
    protected static string $view = 'filament.widgets.onboarding-progress';

    protected static ?int $sort = -1; // Show at the very top

    protected int | string | array $columnSpan = 'full';

    public int $percent = 0;

    public function mount(): void
    {
        $teamId = auth()->user()?->team_id;
        if ($teamId) {
            $team = Team::find($teamId);
            $this->percent = $team?->onboardingPercent() ?? 0;
        }
    }

    /**
     * Only show this widget if onboarding is incomplete (< 100%).
     */
    public static function canView(): bool
    {
        $user = auth()->user();
        if (!$user || !$user->team_id) return false;

        $team = Team::find($user->team_id);
        return $team && $team->onboardingPercent() < 100;
    }
}
