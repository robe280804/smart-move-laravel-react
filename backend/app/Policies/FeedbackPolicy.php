<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Feedback;
use App\Models\User;

class FeedbackPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function view(User $user, Feedback $feedback): bool
    {
        return $user->hasRole('admin') || $user->id === $feedback->user_id;
    }
}
