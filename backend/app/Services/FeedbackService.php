<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\FeedbackDto;
use App\Models\Feedback;
use App\Models\User;
use App\Repositories\Contracts\FeedbackRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class FeedbackService
{
    public function __construct(
        private readonly FeedbackRepositoryInterface $feedbackRepository,
    ) {}

    public function create(User $user, FeedbackDto $dto): Feedback
    {
        return $this->feedbackRepository->create([
            'user_id' => $user->id,
            'rating' => $dto->rating,
            'message' => $dto->message,
        ]);
    }

    public function paginateWithUser(int $perPage = 15): LengthAwarePaginator
    {
        return $this->feedbackRepository->paginateWithUser($perPage);
    }
}
