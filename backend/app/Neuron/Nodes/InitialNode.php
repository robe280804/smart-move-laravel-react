<?php

declare(strict_types=1);

namespace App\Neuron\Nodes;

use App\Neuron\Events\SanitizeInputEvent;
use NeuronAI\Workflow\Node;
use NeuronAI\Workflow\Events\StartEvent;
use NeuronAI\Workflow\WorkflowState;

class InitialNode extends Node
{
    /** @var string[] */
    private const REQUIRED_KEYS = ['user_id', 'fitness_goals', 'schedule', 'equipment'];

    public function __invoke(StartEvent $event, WorkflowState $state): SanitizeInputEvent
    {
        foreach (self::REQUIRED_KEYS as $key) {
            if ($state->get($key) === null) {
                throw new \InvalidArgumentException("Missing required workflow state key: [{$key}]");
            }
        }

        return new SanitizeInputEvent((string) $state->get('user_id'));
    }
}
