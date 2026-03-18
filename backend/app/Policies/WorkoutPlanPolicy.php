<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\WorkoutPlan;

class WorkoutPlanPolicy
{
    /**
     * All authenticated users can list their own plans (the service scopes the query).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, WorkoutPlan $workoutPlan): bool
    {
        return $user->id === $workoutPlan->user_id;
    }

    public function delete(User $user, WorkoutPlan $workoutPlan): bool
    {
        return $user->id === $workoutPlan->user_id;
    }

    public function updateExercise(User $user, WorkoutPlan $workoutPlan): bool
    {
        return $user->id === $workoutPlan->user_id;
    }
}
