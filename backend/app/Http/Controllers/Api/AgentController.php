<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AgentCallRequest;
use App\Http\Resources\WorkoutPlanResource;
use App\Http\Responses\ApiSuccess;
use App\Jobs\GenerateWorkoutPlanJob;
use App\Models\User;
use App\Services\WorkoutPlanService;
use Symfony\Component\HttpFoundation\Response;

class AgentController extends Controller
{
    public function __construct(
        private readonly WorkoutPlanService $workoutPlanService,
    ) {}

    /**
     * 
     * @param AgentCallRequest $request with all the necessary field for generate the workout
     * @return ApiSuccess
     */
    public function generateWorkout(AgentCallRequest $request): ApiSuccess
    {
        $user = $request->user();

        // Create a pending workoutplan with the min field
        $plan = $this->workoutPlanService->createPending($user);

        // Dispach the job
        GenerateWorkoutPlanJob::dispatch($plan, $user, [
            'user_id'       => $user->id,
            'user_email'    => $user->email,
            'fitness_goals' => $request->validated('fitness_goals'),
            'schedule'      => [
                'training_days_per_week' => $request->validated('training_days_per_week'),
                'available_days'         => $request->validated('available_days'),
                'session_duration'       => $request->validated('session_duration'),
            ],
            'equipment'     => [
                'items'      => $request->validated('equipment'),
                'gym_access' => $request->validated('gym_access'),
            ],
            'constraints'   => $request->validated('injuries'),
            'preferences'   => [
                'workout_types'       => $request->validated('workout_type'),
                'sports'              => $request->validated('sports'),
                'preferred_exercises' => $request->validated('preferred_exercises'),
                'additional_notes'    => $request->validated('additional_notes'),
            ],
        ]);

        return new ApiSuccess(
            new WorkoutPlanResource($plan),
            [],
            Response::HTTP_ACCEPTED,
        );
    }
}
