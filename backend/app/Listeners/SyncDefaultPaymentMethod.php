<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Events\WebhookReceived;

class SyncDefaultPaymentMethod
{
    public function handle(WebhookReceived $event): void
    {
        if ($event->payload['type'] !== 'checkout.session.completed') {
            return;
        }

        $session = $event->payload['data']['object'];
        $customerId = $session['customer'] ?? null;
        $subscriptionId = $session['subscription'] ?? null;

        if (! $customerId || ! $subscriptionId) {
            return;
        }

        $user = User::where('stripe_id', $customerId)->first();

        if (! $user) {
            return;
        }

        $localSubscription = $user->subscription('default');

        if (! $localSubscription) {
            return;
        }

        try {
            $stripeSubscription = $localSubscription->asStripeSubscription();

            if ($stripeSubscription->default_payment_method) {
                $user->updateDefaultPaymentMethod($stripeSubscription->default_payment_method);
            }
        } catch (\Throwable $e) {
            Log::warning("Failed to sync default payment method for user {$user->id}: {$e->getMessage()}");
        }
    }
}
