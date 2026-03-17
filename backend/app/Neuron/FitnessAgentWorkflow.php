<?php

declare(strict_types=1);

namespace App\Neuron;

use App\Models\WorkflowInterruptRecord;
use App\Neuron\Nodes\CollectUserInfosNode;
use App\Neuron\Nodes\GenerateWorkoutPlanNode;
use App\Neuron\Nodes\InitialNode;
use NeuronAI\Workflow\Persistence\EloquentPersistence;
use NeuronAI\Workflow\Workflow;
use NeuronAI\Workflow\WorkflowState;

class FitnessAgentWorkflow extends Workflow
{
    /**
     * @throws \NeuronAI\Exceptions\WorkflowException
     */
    public static function create(?string $resumeToken = null, ?WorkflowState $state = null): static
    {
        $persistence = new EloquentPersistence(WorkflowInterruptRecord::class);

        return static::make(
            persistence: $persistence,
            resumeToken: $resumeToken,
            state: $state,
        );
    }

    /**
     * Workflow setup:
     * 1. InitialNode              
     * 2. CollectUserInfosNode     — collects user fitness profile via database
     * 3. GenerateWorkoutPlanNode  — retrieves exercises from Qdrant and calls FitnessAgent
     */
    protected function nodes(): array
    {
        return [
            new InitialNode(),
            app(CollectUserInfosNode::class),
            new GenerateWorkoutPlanNode(),
        ];
    }
}
