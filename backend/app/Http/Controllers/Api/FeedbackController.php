<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Dto\FeedbackDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFeedbackRequest;
use App\Http\Resources\FeedbackResource;
use App\Http\Responses\ApiSuccess;
use App\Models\Feedback;
use App\Services\FeedbackService;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class FeedbackController extends Controller
{
    public function __construct(
        private readonly FeedbackService $feedbackService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Feedback::class);

        $perPage = (int) $request->query('per_page', 15);

        return FeedbackResource::collection($this->feedbackService->paginateWithUser($perPage));
    }

    public function store(StoreFeedbackRequest $request): Responsable
    {
        $this->authorize('create', Feedback::class);

        $dto = FeedbackDto::from($request->validated());
        $feedback = $this->feedbackService->create($request->user(), $dto);

        return new ApiSuccess(
            data: new FeedbackResource($feedback),
            metaData: [],
            statusCode: Response::HTTP_CREATED,
        );
    }
}
