<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Models\Task;
use Filament\Widgets\Widget;

class WelcomeBanner extends Widget
{
    protected static string $view = 'filament.widgets.welcome-banner';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -100;

    public function getViewData(): array
    {
        $user = auth()->user();
        $teamId = $user?->team_id;
        $scope = fn ($q) => $teamId ? $q->where('team_id', $teamId) : $q;

        $firstName = trim(explode(' ', $user?->name ?? '')[0] ?? 'there');
        $hour = (int) now()->format('H');
        $greeting = match(true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default    => 'Good evening',
        };

        $hotLeadsCount = $scope(Lead::query())->where('score', '>=', 70)->count();
        $tasksDueToday = $scope(Task::query())
            ->where('status', '!=', 'done')
            ->whereDate('due_date', today())
            ->count();
        $overdueTasks = $scope(Task::query())
            ->where('status', '!=', 'done')
            ->whereDate('due_date', '<', today())
            ->count();

        return [
            'greeting'      => $greeting,
            'firstName'     => $firstName,
            'hotLeadsCount' => $hotLeadsCount,
            'tasksDueToday' => $tasksDueToday,
            'overdueTasks'  => $overdueTasks,
            'today'         => now()->format('l, F j'),
        ];
    }
}
