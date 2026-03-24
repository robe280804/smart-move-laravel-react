<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\AlreadySubscribedException;
use App\Models\User;
use Laravel\Cashier\Checkout;

class PaymentService
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
    ) {}

    public function getCurrentPlan(User $user): string
    {
        return $this->subscriptionService->getPlan($user)->value;
    }

    public function getBillingPortalUrl(User $user): string
    {
        return $user->billingPortalUrl(config('plans.success_url'));
    }

    /**
     * Creates a Stripe Checkout Session for new subscribers, or swaps the price
     * for users who already have an active subscription on a different plan.
     * Returns null when a swap is performed (no redirect needed).
     *
     * @throws AlreadySubscribedException
     */
    public function checkout(User $user, string $plan): ?Checkout
    {
        $priceId = config("plans.stripe_prices.{$plan}");

        if ($user->subscribedToPrice($priceId, 'default')) {
            throw new AlreadySubscribedException('You are already subscribed to this plan.');
        }

        $existingSubscription = $user->subscriptions()
            ->whereIn('stripe_status', ['active', 'trialing', 'incomplete', 'past_due'])
            ->latest()
            ->first();

        if ($existingSubscription !== null) {
            $existingSubscription->swap($priceId);

            return null;
        }

        return $user->newSubscription('default', $priceId)
            ->checkout([
                'success_url' => config('plans.success_url'),
                'cancel_url' => config('plans.cancel_url'),
            ]);
    }
}
