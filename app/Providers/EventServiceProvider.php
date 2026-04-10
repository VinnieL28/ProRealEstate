<?php

namespace App\Providers;

use App\Events\DealClosedEvent;
use App\Events\LeadCreatedEvent;
use App\Events\LeadStageChangedEvent;
use App\Listeners\WorkflowEngine;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        LeadCreatedEvent::class => [
            [WorkflowEngine::class, 'handleLeadCreated'],
        ],
        LeadStageChangedEvent::class => [
            [WorkflowEngine::class, 'handleLeadStageChanged'],
        ],
        DealClosedEvent::class => [
            [WorkflowEngine::class, 'handleDealClosed'],
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
