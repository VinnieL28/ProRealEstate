<?php

namespace App\Providers;

use App\Models\ColdLead;
use App\Models\Contact;
use App\Models\SellerLead;
use App\Models\Transaction;
use App\Observers\ColdLeadObserver;
use App\Observers\ContactObserver;
use App\Observers\SellerLeadObserver;
use App\Observers\TransactionObserver;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Livewire::component('ai-chat', \App\Livewire\AiChat::class);

        Contact::observe(ContactObserver::class);
        ColdLead::observe(ColdLeadObserver::class);
        SellerLead::observe(SellerLeadObserver::class);
        Transaction::observe(TransactionObserver::class);
    }
}
