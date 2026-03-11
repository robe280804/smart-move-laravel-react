<?php

declare(strict_types=1);

namespace App\Neuron;

use App\Neuron\Nodes\CollectUserInfosNode;
use App\Neuron\Nodes\InitialNode;
use NeuronAI\Workflow\Workflow;

class FitnessAgentWorkflow extends Workflow
{
    /**
     * Workflow setup:
     * 1. InitialNode       — sanitizes the raw user message
     * 2. CollectUserInfosNode — loads user profile data from the database
     */
    protected function nodes(): array
    {
        return [
            new InitialNode(),
            new CollectUserInfosNode(),
        ];
    }
}
