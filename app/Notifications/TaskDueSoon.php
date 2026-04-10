<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskDueSoon extends Notification
{
    use Queueable;

    public function __construct(public Task $task) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'Task Due Soon',
            'message' => 'Task "' . $this->task->title . '" is due ' . ($this->task->due_date?->diffForHumans() ?? 'soon'),
            'url'     => route('filament.admin.resources.tasks.edit', $this->task),
            'icon'    => 'heroicon-o-clock',
        ];
    }
}
