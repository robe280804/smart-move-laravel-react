<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Feedback;
use App\Repositories\Contracts\FeedbackRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class FeedbackRepository implements FeedbackRepositoryInterface
{
    public function create(array $data): Feedback
    {
        return Feedback::query()->create($data);
    }

    public function paginateWithUser(int $perPage = 15): LengthAwarePaginator
    {
        return Feedback::query()->with('user')->latest()->paginate($perPage);
    }
}
