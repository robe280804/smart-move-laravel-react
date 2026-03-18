<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBlockExerciseRequest;
use App\Http\Resources\BlockExerciseResource;
use App\Http\Responses\ApiSuccess;
use App\Models\BlockExercise;
use App\Models\WorkoutPlan;
use App\Services\SubscriptionService;
use App\Services\WorkoutPlanService;
use Illuminate\Contracts\Support\Responsable;
use Symfony\Component\HttpFoundation\Response;

class BlockExerciseController extends Controller
{
    public function __construct(
        private readonly WorkoutPlanService $workoutPlanService,
        private readonly SubscriptionService $subscriptionService,
    ) {}

    public function update(
        UpdateBlockExerciseRequest $request,
        WorkoutPlan $workoutPlan,
        BlockExercise $blockExercise,
    ): Responsable {
        $this->authorize('updateExercise', $workoutPlan);

        if (! $this->subscriptionService->getPlan($request->user())->canEditExercises()) {
            abort(Response::HTTP_FORBIDDEN, 'Exercise editing is not available on your current plan.');
        }

        $updated = $this->workoutPlanService->updateBlockExercise($workoutPlan, $blockExercise, $request->validated());

        return new ApiSuccess(
            data: new BlockExerciseResource($updated),
            metaData: [],
            statusCode: Response::HTTP_OK,
        );
    }
}
