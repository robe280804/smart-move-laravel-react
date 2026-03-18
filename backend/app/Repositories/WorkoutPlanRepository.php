<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use App\Models\WorkoutPlan;
use App\Repositories\Contracts\WorkoutPlanRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class WorkoutPlanRepository implements WorkoutPlanRepositoryInterface
{
    public function create(array $data): WorkoutPlan
    {
        return WorkoutPlan::query()->create($data);
    }

    public function findById(int $id): ?WorkoutPlan
    {
        return WorkoutPlan::query()->find($id);
    }

    public function findByIdWithRelations(int $id): ?WorkoutPlan
    {
        return WorkoutPlan::query()
            ->with('planDays.workoutBlocks.blockExercises.exercise')
            ->find($id);
    }

    /** @return Collection<int, WorkoutPlan> */
    public function findByUser(User $user): Collection
    {
        return WorkoutPlan::query()->where('user_id', $user->id)->get();
    }

    /** @return Collection<int, WorkoutPlan> */
    public function findByUserWithRelations(User $user): Collection
    {
        return WorkoutPlan::query()
            ->where('user_id', $user->id)
            ->with('planDays.workoutBlocks.blockExercises.exercise')
            ->get();
    }

    /** @return Collection<int, WorkoutPlan> */
    public function findByUserWithRelationsSince(User $user, ?Carbon $since): Collection
    {
        return WorkoutPlan::query()
            ->where('user_id', $user->id)
            ->when($since !== null, fn ($q) => $q->where('created_at', '>=', $since))
            ->with('planDays.workoutBlocks.blockExercises.exercise')
            ->get();
    }

    public function delete(WorkoutPlan $workoutPlan): void
    {
        $workoutPlan->delete();
    }
}
