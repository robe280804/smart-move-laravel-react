<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\FitnessInfo;
use App\Models\User;

class FitnessInfoPolicy
{
    public function view(User $user, FitnessInfo $fitnessInfo): bool
    {
        return $user->id === $fitnessInfo->user_id;
    }

    public function create(User $user): bool
    {
        return $user->fitnessInfo()->doesntExist();
    }

    public function update(User $user, FitnessInfo $fitnessInfo): bool
    {
        return $user->id === $fitnessInfo->user_id;
    }

    public function delete(User $user, FitnessInfo $fitnessInfo): bool
    {
        return $user->id === $fitnessInfo->user_id;
    }
}
