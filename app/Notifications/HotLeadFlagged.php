<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class HotLeadFlagged extends Notification
{
    use Queueable;

    public function __construct(public Lead $lead) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $name = $this->lead->owner_name ?: trim($this->lead->first_name . ' ' . $this->lead->last_name) ?: 'Unknown';

        return [
            'title'   => '🔥 Hot Lead Alert',
            'message' => $name . ' is now a hot lead (score: ' . number_format($this->lead->hot_score, 1) . '/10). Follow up now!',
            'url'     => route('filament.admin.resources.leads.edit', $this->lead),
            'icon'    => 'heroicon-o-fire',
        ];
    }
}
