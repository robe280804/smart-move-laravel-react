<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\User;
use App\Models\WorkoutPlan;
use Illuminate\Database\Eloquent\Collection;

interface WorkoutPlanRepositoryInterface
{
    public function create(array $data): WorkoutPlan;

    public function findById(int $id): ?WorkoutPlan;

    /** @return Collection<int, WorkoutPlan> */
    public function findByUser(User $user): Collection;
}
