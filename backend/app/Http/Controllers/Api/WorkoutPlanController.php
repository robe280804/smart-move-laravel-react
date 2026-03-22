<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WorkoutPlanResource;
use App\Http\Responses\ApiSuccess;
use App\Models\WorkoutPlan;
use App\Services\SubscriptionService;
use App\Services\WorkoutPlanService;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class WorkoutPlanController extends Controller
{
    public function __construct(
        private readonly WorkoutPlanService $workoutPlanService,
        private readonly SubscriptionService $subscriptionService,
    ) {}

    public function index(Request $request): Responsable
    {
        $this->authorize('viewAny', WorkoutPlan::class);

        $since = $this->subscriptionService->historyDateLimit($request->user());
        $plans = $this->workoutPlanService->getAll($request->user(), $since);

        return new ApiSuccess(
            data: WorkoutPlanResource::collection($plans),
            metaData: [],
            statusCode: SymfonyResponse::HTTP_OK,
        );
    }

    public function show(WorkoutPlan $workoutPlan): Responsable
    {
        $this->authorize('view', $workoutPlan);

        $this->workoutPlanService->loadRelations($workoutPlan);

        return new ApiSuccess(
            data: new WorkoutPlanResource($workoutPlan),
            metaData: [],
            statusCode: SymfonyResponse::HTTP_OK,
        );
    }

    public function exportPdf(Request $request, WorkoutPlan $workoutPlan): Response
    {
        $this->authorize('exportPdf', $workoutPlan);

        if (! $this->subscriptionService->getPlan($request->user())->canExportPdf()) {
            abort(SymfonyResponse::HTTP_FORBIDDEN, 'PDF export is not available on your current plan.');
        }

        return $this->workoutPlanService->generatePdf($workoutPlan);
    }

    public function destroy(WorkoutPlan $workoutPlan): Responsable
    {
        $this->authorize('delete', $workoutPlan);

        $this->workoutPlanService->delete($workoutPlan);

        return new ApiSuccess(
            data: null,
            metaData: ['message' => 'Workout plan deleted successfully.'],
            statusCode: SymfonyResponse::HTTP_NO_CONTENT,
        );
    }
}
