<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;

class StripeWebhookController extends CashierWebhookController
{
    /**
     * Handle successful payment — activate team subscription.
     */
    protected function handleInvoicePaymentSucceeded(array $payload): \Symfony\Component\HttpFoundation\Response
    {
        $stripeId = $payload['data']['object']['customer'] ?? null;

        if ($stripeId) {
            $team = Team::where('stripe_id', $stripeId)->first();
            if ($team) {
                // Determine plan from subscription metadata / amount
                $amount = $payload['data']['object']['amount_paid'] ?? 0;
                $plan = match(true) {
                    $amount >= 19900 => 'enterprise',
                    $amount >= 7900  => 'pro',
                    default          => 'starter',
                };
                $team->update(['subscription_plan' => $plan, 'is_active' => true]);
                Log::info("Team {$team->id} subscription activated: {$plan}");
            }
        }

        return parent::handleInvoicePaymentSucceeded($payload);
    }

    /**
     * Handle payment failure — notify but don't immediately revoke access.
     */
    protected function handleInvoicePaymentFailed(array $payload): \Symfony\Component\HttpFoundation\Response
    {
        $stripeId = $payload['data']['object']['customer'] ?? null;

        if ($stripeId) {
            $team = Team::where('stripe_id', $stripeId)->first();
            if ($team) {
                Log::warning("Payment failed for team {$team->id}");
                // Optionally: send notification to team owner
            }
        }

        return parent::handleInvoicePaymentFailed($payload);
    }

    /**
     * Handle subscription cancellation — revert to trial state.
     */
    protected function handleCustomerSubscriptionDeleted(array $payload): \Symfony\Component\HttpFoundation\Response
    {
        $stripeId = $payload['data']['object']['customer'] ?? null;

        if ($stripeId) {
            $team = Team::where('stripe_id', $stripeId)->first();
            if ($team) {
                $team->update(['subscription_plan' => null]);
                Log::info("Team {$team->id} subscription cancelled via webhook");
            }
        }

        return parent::handleCustomerSubscriptionDeleted($payload);
    }
}
