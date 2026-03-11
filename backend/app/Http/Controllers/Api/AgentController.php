<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiSuccess;
use App\Neuron\FitnessAgent;
use App\Neuron\FitnessAgentWorkflow;
use Illuminate\Http\Request;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Workflow\WorkflowState;
use Symfony\Component\HttpFoundation\Response;

class AgentController extends Controller
{
    public function call(Request $request): ApiSuccess
    {
        $state = new WorkflowState();
        $state->set('user_message', $request->input('message', ''));

        $workflow = FitnessAgentWorkflow::make(state: $state);
        foreach ($workflow->init()->run() as $_) {
        }

        $sanitized = $state->get('user_message');

        $message = FitnessAgent::make()
            ->chat(new UserMessage($sanitized))
            ->getMessage();

        return new ApiSuccess(
            null,
            ['response' => $message->getContent()],
            Response::HTTP_OK
        );
    }
}
