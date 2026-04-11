<?php

namespace App\Filament\Pages;

use App\Models\Team;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Laravel\Cashier\Subscription;

class BillingPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Billing';
    protected static ?string $slug = 'billing';
    protected static string $view = 'filament.pages.billing';
    protected static ?int $navigationSort = 20;

    public array $plans = [];
    public ?array $currentSubscription = null;
    public ?Team $team = null;
    public bool $onTrial = false;
    public ?string $trialEndsAt = null;
    public int $leadCount = 0;
    public int $userCount = 0;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && in_array($user->role, ['super_admin', 'owner', 'admin'], true);
    }

    public function mount(): void
    {
        $user      = auth()->user();
        $this->team = Team::find($user?->team_id);

        $this->plans = [
            'starter'    => ['name' => 'Starter',    'price' => 29,  'agents' => 3,   'leads' => 500,    'stripe_price' => config('cashier.plans.starter',    'price_starter')],
            'pro'        => ['name' => 'Pro',         'price' => 79,  'agents' => 10,  'leads' => 5000,   'stripe_price' => config('cashier.plans.pro',         'price_pro')],
            'enterprise' => ['name' => 'Enterprise',  'price' => 199, 'agents' => 999, 'leads' => 999999, 'stripe_price' => config('cashier.plans.enterprise',  'price_enterprise')],
        ];

        if ($this->team) {
            $this->onTrial    = $this->team->isOnTrial();
            $this->trialEndsAt = $this->team->trial_ends_at?->format('M d, Y');
            $this->leadCount  = $this->team->leads()->count();
            $this->userCount  = $this->team->users()->count();

            $sub = $this->team->subscriptions()->active()->first();
            if ($sub) {
                $this->currentSubscription = [
                    'plan'       => $this->team->subscription_plan ?? 'unknown',
                    'ends_at'    => $sub->ends_at?->format('M d, Y'),
                    'renews_at'  => $sub->created_at?->addMonth()->format('M d, Y'),
                    'cancelled'  => $sub->cancelled(),
                ];
            }
        }
    }

    public function subscribe(string $plan): void
    {
        if (!$this->team) {
            Notification::make()->title('No team found')->danger()->send();
            return;
        }

        $priceId = $this->plans[$plan]['stripe_price'] ?? null;
        if (!$priceId) {
            Notification::make()->title('Invalid plan or Stripe price not configured')->danger()->send();
            return;
        }

        try {
            // Generate a Stripe Checkout session URL
            $checkoutUrl = $this->team
                ->newSubscription('default', $priceId)
                ->checkout([
                    'success_url' => url('/admin/billing?success=1'),
                    'cancel_url'  => url('/admin/billing?cancelled=1'),
                ])
                ->url;

            $this->redirect($checkoutUrl);
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Stripe error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function cancelSubscription(): void
    {
        if (!$this->team) return;

        try {
            $this->team->subscription('default')->cancel();
            $this->team->update(['subscription_plan' => 'trial']);
            Notification::make()->title('Subscription cancelled — access continues until end of billing period')->warning()->send();
            $this->mount();
        } catch (\Throwable $e) {
            Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
        }
    }
}
