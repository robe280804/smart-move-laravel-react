<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use App\Models\WorkoutPlan;
use App\Repositories\Contracts\WorkoutPlanRepositoryInterface;
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

    /** @return Collection<int, WorkoutPlan> */
    public function findByUser(User $user): Collection
    {
        return WorkoutPlan::query()->where('user_id', $user->id)->get();
    }
}
