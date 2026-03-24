<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SubscriptionPlan;
use App\Enums\WorkoutPlanStatus;
use App\Models\User;
use Carbon\Carbon;

class SubscriptionService
{
    public function getPlan(User $user): SubscriptionPlan
    {
        $subscription = $user->subscriptions()->active()->latest()->first();

        if ($subscription === null) {
            return SubscriptionPlan::Free;
        }

        return match ($subscription->stripe_price) {
            config('plans.stripe_prices.advanced') => SubscriptionPlan::Advanced,
            config('plans.stripe_prices.pro')      => SubscriptionPlan::Pro,
            default                                => SubscriptionPlan::Free,
        };
    }

    public function canGenerate(User $user): bool
    {
        $plan  = $this->getPlan($user);
        $limit = $plan->generationLimit();

        if ($limit === null) {
            return true;
        }

        $count = $user->workoutPlans()
            ->where('status', '!=', WorkoutPlanStatus::Failed)
            // If not free plan, count only the plan in this mounth
            ->when($plan !== SubscriptionPlan::Free, function ($query): void {
                $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
            })
            ->count();

        return $count < $limit;
    }

    public function canSaveActivePlan(User $user): bool
    {
        $plan  = $this->getPlan($user);
        $limit = $plan->activePlansLimit();

        if ($limit === null) {
            return true;
        }

        $count = $user->workoutPlans()
            ->where('status', WorkoutPlanStatus::Completed)
            ->count();

        return $count < $limit;
    }

    public function historyDateLimit(User $user): ?Carbon
    {
        $days = $this->getPlan($user)->historyDaysLimit();

        return $days !== null ? Carbon::now()->subDays($days) : null;
    }
}
