<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AgentCallRequest;
use App\Http\Requests\AgentResumeRequest;
use App\Http\Responses\ApiSuccess;
use App\Neuron\FitnessAgentWorkflow;
use Illuminate\Http\JsonResponse;
use NeuronAI\Workflow\Interrupt\Action;
use NeuronAI\Workflow\Interrupt\ActionDecision;
use NeuronAI\Workflow\Interrupt\ApprovalRequest;
use NeuronAI\Workflow\Interrupt\WorkflowInterrupt;
use NeuronAI\Workflow\WorkflowState;
use Symfony\Component\HttpFoundation\Response;

class AgentController extends Controller
{
    public function call(AgentCallRequest $request): ApiSuccess|JsonResponse
    {
        $state = new WorkflowState();
        $state->set('user_message', $request->validated('message'));
        $state->set('user_id', $request->user()?->id);

        try {
            $workflow = FitnessAgentWorkflow::create(state: $state);
            $workflow->init()->run();
        } catch (WorkflowInterrupt $interrupt) {
            return $this->interruptResponse($interrupt);
        }

        return $this->agentResponse($state);
    }

    public function resume(AgentResumeRequest $request): ApiSuccess|JsonResponse
    {
        $resumeToken = $request->validated('resume_token');
        $resumeRequest = $this->buildApprovalRequest($request->validated('actions', []));

        try {
            $state = FitnessAgentWorkflow::create(resumeToken: $resumeToken)
                ->init($resumeRequest)
                ->run();
        } catch (WorkflowInterrupt $interrupt) {
            return $this->interruptResponse($interrupt);
        }

        return $this->agentResponse($state);
    }

    private function interruptResponse(WorkflowInterrupt $interrupt): JsonResponse
    {
        return response()->json([
            'status' => 'interrupted',
            'resume_token' => $interrupt->getResumeToken(),
            'message' => $interrupt->getMessage(),
            'actions' => json_decode($interrupt->getRequest()->jsonSerialize()['actions'], true),
        ], Response::HTTP_ACCEPTED);
    }

    private function agentResponse(WorkflowState $state): ApiSuccess
    {
        return new ApiSuccess(
            null,
            ['response' => $state->get('agent_response', '')],
            Response::HTTP_OK
        );
    }

    /**
     * @param array<int, array{id: string, decision: string, feedback?: string|null}> $actionsData
     */
    private function buildApprovalRequest(array $actionsData): ApprovalRequest
    {
        $resumeRequest = new ApprovalRequest('');

        foreach ($actionsData as $actionData) {
            $action = new Action(
                id: $actionData['id'],
                name: $actionData['id'],
                decision: ActionDecision::from($actionData['decision']),
                feedback: $actionData['feedback'] ?? null,
            );
            $resumeRequest->addAction($action);
        }

        return $resumeRequest;
    }
}
