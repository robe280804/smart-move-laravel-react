<?php

declare(strict_types=1);

namespace App\Neuron\Nodes;

use App\Neuron\Events\SanitizeInputEvent;
use NeuronAI\Workflow\Node;
use NeuronAI\Workflow\Events\StartEvent;
use NeuronAI\Workflow\WorkflowState;

class InitialNode extends Node
{
    public function __invoke(StartEvent $event, WorkflowState $state): SanitizeInputEvent
    {
        return new SanitizeInputEvent('');
    }
}
