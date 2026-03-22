<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AgentCallRequest;
use App\Http\Resources\WorkoutPlanResource;
use App\Http\Responses\ApiSuccess;
use App\Jobs\GenerateWorkoutPlanJob;
use App\Services\SubscriptionService;
use App\Services\WorkoutPlanService;
use Symfony\Component\HttpFoundation\Response;

class AgentController extends Controller
{
    public function __construct(
        private readonly WorkoutPlanService $workoutPlanService,
        private readonly SubscriptionService $subscriptionService,
    ) {}

    /**
     * @param  AgentCallRequest  $request  with all the necessary field for generate the workout
     */
    public function generateWorkout(AgentCallRequest $request): ApiSuccess
    {
        $user = $request->user();

        if (! $this->subscriptionService->canGenerate($user)) {
            abort(Response::HTTP_FORBIDDEN, 'You have reached your workout generation limit.');
        }

        if (! $this->subscriptionService->canSaveActivePlan($user)) {
            abort(Response::HTTP_FORBIDDEN, 'You have reached your active plans limit.');
        }

        if ($user->fitnessInfo()->first() === null) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Please complete your fitness profile before generating a workout plan.');
        }

        $generationRequest = [
            'fitness_goals' => $request->validated('fitness_goals'),
            'schedule' => [
                'training_days_per_week' => $request->validated('training_days_per_week'),
                'available_days' => $request->validated('available_days'),
                'session_duration' => $request->validated('session_duration'),
            ],
            'equipment' => [
                'items' => $request->validated('equipment'),
                'gym_access' => $request->validated('gym_access'),
            ],
            'constraints' => $request->validated('injuries'),
            'preferences' => [
                'workout_types' => $request->validated('workout_type'),
                'sports' => $request->validated('sports'),
                'preferred_exercises' => $request->validated('preferred_exercises'),
                'additional_notes' => $request->validated('additional_notes'),
            ],
        ];

        $plan = $this->workoutPlanService->createPending($user, $generationRequest);

        $queue = $this->subscriptionService->getPlan($user)->hasPriorityGeneration() ? 'high' : 'default';

        GenerateWorkoutPlanJob::dispatch($plan, $user, [
            'user_id' => $user->id,
            'user_email' => $user->email,
            ...$generationRequest,
        ])->onQueue($queue);

        return new ApiSuccess(
            new WorkoutPlanResource($plan),
            [],
            Response::HTTP_ACCEPTED,
        );
    }
}
