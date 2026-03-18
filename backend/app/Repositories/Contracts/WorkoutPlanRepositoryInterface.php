<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\User;
use App\Models\WorkoutPlan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

interface WorkoutPlanRepositoryInterface
{
    public function create(array $data): WorkoutPlan;

    public function findById(int $id): ?WorkoutPlan;

    public function findByIdWithRelations(int $id): ?WorkoutPlan;

    /** @return Collection<int, WorkoutPlan> */
    public function findByUser(User $user): Collection;

    /** @return Collection<int, WorkoutPlan> */
    public function findByUserWithRelations(User $user): Collection;

    /** @return Collection<int, WorkoutPlan> */
    public function findByUserWithRelationsSince(User $user, ?Carbon $since): Collection;

    public function delete(WorkoutPlan $workoutPlan): void;
}
