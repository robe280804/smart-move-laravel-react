<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\WorkoutPlan;

class WorkoutPlanPolicy
{
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
