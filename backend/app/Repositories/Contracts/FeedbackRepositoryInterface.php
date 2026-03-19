<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Feedback;
use Illuminate\Pagination\LengthAwarePaginator;

interface FeedbackRepositoryInterface
{
    public function create(array $data): Feedback;

    public function paginateWithUser(int $perPage = 15): LengthAwarePaginator;
}
