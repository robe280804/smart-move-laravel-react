<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Users can only view their own profile.
     */
    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }

    /**
     * Users can only update their own profile.
     */
    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }

    /**
     * Users can only delete their own account.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }
}
