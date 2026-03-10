<?php

declare(strict_types=1);

namespace App\Neuron\Nodes;

use NeuronAI\Workflow\Node;
use NeuronAI\Workflow\Events\StartEvent;
use NeuronAI\Workflow\Events\StopEvent;
use NeuronAI\Workflow\WorkflowState;

class CollectUserInfosNode extends Node
{
    /**
     * Here i will collect all the user info i need for generate the schema
     * I will load the user based info from the database like height ... if i can't find them i will ask to him
     */
    public function __invoke(StartEvent $event, WorkflowState $state): StopEvent
    {
        // ...

        return new StopEvent();
    }
}
