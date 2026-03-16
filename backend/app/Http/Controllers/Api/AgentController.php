<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AgentCallRequest;
use App\Http\Resources\WorkoutPlanResource;
use App\Http\Responses\ApiSuccess;
use App\Models\User;
use App\Neuron\FitnessAgentWorkflow;
use App\Services\WorkoutPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use NeuronAI\Workflow\WorkflowState;
use Symfony\Component\HttpFoundation\Response;

class AgentController extends Controller
{
    public function __construct(
        private readonly WorkoutPlanService $workoutPlanService,
    ) {}

    public function generateWorkout(AgentCallRequest $request): ApiSuccess|JsonResponse
    {
        Log::info('controller', [
            'req' => $request
        ]);

        set_time_limit(0);

        $state = new WorkflowState();

        // Set user 
        $state->set('user_id', $request->user()?->id);
        $state->set('user_email', $request->user()?->email);

        // Set workout datas
        $state->set('fitness_goals', $request->validated('fitness_goals'));
        $state->set('schedule', [
            'training_days_per_week' => $request->validated('training_days_per_week'),
            'available_days'         => $request->validated('available_days'),
            'session_duration'       => $request->validated('session_duration'),
        ]);
        $state->set('equipment', [
            'items'      => $request->validated('equipment'),
            'gym_access' => $request->validated('gym_access'),
        ]);
        $state->set('constraints', $request->validated('injuries'));
        $state->set('preferences', [
            'workout_types'       => $request->validated('workout_type'),
            'sports'              => $request->validated('sports'),
            'preferred_exercises' => $request->validated('preferred_exercises'),
            'additional_notes'    => $request->validated('additional_notes'),
        ]);

        Log::info('controller validation passed');

        $workflow = FitnessAgentWorkflow::create(state: $state);
        $workflow->init()->run();

        /** @var User $user */
        $user = $request->user();

        $workoutPlan = $this->workoutPlanService->createFromAgentResponse(
            (string) $state->get('agent_response', ''),
            $user,
        );

        return new ApiSuccess(
            new WorkoutPlanResource($workoutPlan),
            [],
            Response::HTTP_CREATED,
        );
    }
}
