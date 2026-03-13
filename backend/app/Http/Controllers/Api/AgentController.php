<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AgentCallRequest;
use App\Http\Responses\ApiSuccess;
use App\Neuron\FitnessAgentWorkflow;
use Illuminate\Http\JsonResponse;
use NeuronAI\Workflow\WorkflowState;
use Symfony\Component\HttpFoundation\Response;

class AgentController extends Controller
{
    public function call(AgentCallRequest $request): ApiSuccess|JsonResponse
    {
        $state = new WorkflowState();
        $state->set('user_message', $request->validated('message', ''));

        $state->set('user_id', $request->user()?->id);
        $state->set('user_email', $request->user()?->email);

        $state->set('fitness_goal', $request->validated('fitness_goal'));
        $state->set('schedule', $request->validated('schedule'));
        $state->set('equipment', $request->validated('equipment'));
        $state->set('constraints', $request->validated('constraints'));
        $state->set('preferences', $request->validated('preferences'));

        $workflow = FitnessAgentWorkflow::create(state: $state);
        $workflow->init()->run();

        return new ApiSuccess(
            null,
            ['response' => $state->get('agent_response', '')],
            Response::HTTP_OK
        );
    }
}
