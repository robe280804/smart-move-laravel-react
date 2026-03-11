<?php

declare(strict_types=1);

namespace App\Neuron\Nodes;

use App\Neuron\Events\SanitizeInputEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use NeuronAI\Workflow\Node;
use NeuronAI\Workflow\Events\StartEvent;
use NeuronAI\Workflow\WorkflowState;

class InitialNode extends Node
{

    private $blocked_patterns = [
        "system prompt",
        "your instructions",
        "show your prompt",
        "repeat the system message"
    ];

    /**
     * Sanitizes the raw user message: strips tags, removes null bytes, and trims whitespace.
     */
    public function __invoke(StartEvent $event, WorkflowState $state): SanitizeInputEvent
    {
        $raw = (string) $state->get('user_message', '');

        $state = $this->storeUserInfo($state);

        $sanitized = strip_tags($raw);        // removes any HTML tags
        $sanitized = str_replace("\0", '', $sanitized); // removes null bytes
        $sanitized = trim($sanitized);        // removes leading/trailing whitespace
        $sanitized = mb_substr($sanitized, 0, 2000); // limit to 10000 chars
        $sanitized = preg_replace("/\r\n|\r|\n/", "\n", $sanitized);
        $sanitized = preg_replace(
            '/(ignore\s+previous\s+instructions|
                    forget\s+previous\s+instructions|
                    reveal\s+(your\s+)?prompt|
                    show\s+(your\s+)?instructions|
                    print\s+(the\s+)?system\s+message|
                    repeat\s+(the\s+)?system\s+message|
                    what\s+are\s+your\s+rules|
                    what\s+were\s+you\s+told|
                    disclose\s+internal)/ix',
            '',
            $sanitized
        );

        // Malicius prompt
        foreach ($this->blocked_patterns as $pattern) {
            if (stripos($sanitized, $pattern) !== false) {
                Log::warning('Prompt extraction attempt', [
                    'email' => $state->get('user_email'),
                    'msg' => $sanitized
                ]);

                $state->set('user_message', 'PROMPT_EXTRACTION_BLOCKED');

                return new SanitizeInputEvent(
                    "Sorry, I can't share my internal instructions."
                );
            }
        }

        $state->set('user_message', $sanitized);

        Log::debug('User sanitize msg', [
            'email' => $state->get('user_email'),
            'msg' => $sanitized
        ]);

        return new SanitizeInputEvent($sanitized);
    }


    private function storeUserInfo(WorkflowState $state): WorkflowState
    {
        $user = Auth::user();

        if ($user) {
            $state->set('user_id', $user->id);
            $state->set('user_email', $user->email);
        }
        return $state;
    }
}
