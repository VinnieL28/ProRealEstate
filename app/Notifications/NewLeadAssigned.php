<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewLeadAssigned extends Notification
{
    use Queueable;

    public function __construct(public Lead $lead) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'New Lead Assigned',
            'message' => 'You have been assigned lead: ' . ($this->lead->owner_name ?: trim($this->lead->first_name . ' ' . $this->lead->last_name) ?: 'Unknown'),
            'url'     => route('filament.admin.resources.leads.edit', $this->lead),
            'icon'    => 'heroicon-o-user-plus',
        ];
    }
}
