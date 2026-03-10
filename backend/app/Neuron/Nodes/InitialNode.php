<?php

declare(strict_types=1);

namespace App\Neuron\Nodes;

use App\Neuron\Event\SanitizeInputEvent;
use Illuminate\Support\Facades\Log;
use NeuronAI\Workflow\Node;
use NeuronAI\Workflow\Events\StartEvent;
use NeuronAI\Workflow\WorkflowState;

class InitialNode extends Node
{
    /**
     * Sanitizes the raw user message: strips tags, removes null bytes, and trims whitespace.
     */
    public function __invoke(StartEvent $event, WorkflowState $state): SanitizeInputEvent
    {
        $raw = (string) $state->get('user_message', '');

        $sanitized = strip_tags($raw);        // removes any HTML tags
        $sanitized = str_replace("\0", '', $sanitized); // removes null bytes
        $sanitized = trim($sanitized);        // removes leading/trailing whitespace
        $sanitized = mb_substr($sanitized, 0, 10000); // limit to 10000 chars
        $sanitized = preg_replace("/\r\n|\r|\n/", "\n", $sanitized);
        $sanitized = preg_replace('/(ignore previous instructions|do .* now)/i', '', $sanitized);

        $state->set('user_message', $sanitized);

        Log::debug('User sanitize msg', [$sanitized]);

        return new SanitizeInputEvent($sanitized);
    }
}
