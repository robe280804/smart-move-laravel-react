<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\AlreadySubscribedException;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Laravel\Cashier\Checkout;
use Laravel\Cashier\Subscription;

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
     * @throws InvalidArgumentException
     */
    public function checkout(User $user, string $plan): ?Checkout
    {
        $priceId = config("plans.stripe_prices.{$plan}");

        if ($priceId === null || $priceId === '') {
            throw new InvalidArgumentException("No Stripe price configured for plan: {$plan}");
        }

        if ($user->subscribedToPrice($priceId, 'default')) {
            throw new AlreadySubscribedException('You are already subscribed to this plan.');
        }

        // Only swap subscriptions that have a confirmed payment.
        // Incomplete subscriptions (first payment not yet confirmed) are cancelled instead.
        $activeSubscriptions = $user->subscriptions()
            ->whereIn('stripe_status', ['active', 'trialing', 'past_due'])
            ->latest()
            ->get();

        if ($activeSubscriptions->isNotEmpty()) {
            $primary = $activeSubscriptions->first();
            $primary->swap($priceId);

            $this->cancelOtherSubscriptions($user, $activeSubscriptions->skip(1));

            return null;
        }

        // Cancel any leftover incomplete subscriptions before creating a new checkout.
        $this->cancelIncompleteSubscriptions($user);

        return $user->newSubscription('default', $priceId)
            ->checkout([
                'success_url' => config('plans.success_url'),
                'cancel_url' => config('plans.cancel_url'),
            ]);
    }

    /**
     * @param  Collection<int, Subscription>  $subscriptions
     */
    private function cancelOtherSubscriptions(User $user, $subscriptions): void
    {
        $subscriptions->each(function ($subscription) use ($user): void {
            try {
                $subscription->cancelNow();
            } catch (\Throwable $e) {
                Log::warning("Failed to cancel duplicate subscription {$subscription->stripe_id} for user {$user->id}: {$e->getMessage()}");
            }
        });
    }

    private function cancelIncompleteSubscriptions(User $user): void
    {
        $user->subscriptions()
            ->where('stripe_status', 'incomplete')
            ->get()
            ->each(function ($subscription) use ($user): void {
                try {
                    $subscription->cancelNow();
                } catch (\Throwable $e) {
                    Log::warning("Failed to cancel incomplete subscription {$subscription->stripe_id} for user {$user->id}: {$e->getMessage()}");
                }
            });
    }
}
