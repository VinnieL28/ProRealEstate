<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Lead;
use App\Models\Property;
use App\Models\Deal;
use App\Models\Task;
use App\Models\CallLog;
use App\Models\SmsLog;
use App\Models\Setting;
use App\Models\Attachment;
use App\Models\User;
use App\Policies\LeadPolicy;
use App\Policies\UserPolicy;
use App\Policies\PropertyPolicy;
use App\Policies\DealPolicy;
use App\Policies\TaskPolicy;
use App\Policies\CallLogPolicy;
use App\Policies\SmsLogPolicy;
use App\Policies\SettingPolicy;
use App\Policies\AttachmentPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Lead::class => LeadPolicy::class,
        Property::class => PropertyPolicy::class,
        Deal::class => DealPolicy::class,
        Task::class => TaskPolicy::class,
        CallLog::class => CallLogPolicy::class,
        SmsLog::class => SmsLogPolicy::class,
        Setting::class => SettingPolicy::class,
        Attachment::class => AttachmentPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
