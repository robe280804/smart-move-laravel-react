<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Ai\Agents\Prompts\BackgroundPrompt;
use App\Ai\Agents\Prompts\OutputPrompt;
use App\Ai\Agents\Prompts\StepsPrompt;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider('anthropic')]
#[Timeout(600)]
#[MaxTokens(16000)]
class FitnessAgent implements Agent
{
    use Promptable;

    /**
     * Get the model to use for generation.
     */
    public function model(): string
    {
        return config('services.claude.model', 'claude-sonnet-4-6');
    }

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $background = implode("\n", BackgroundPrompt::content());
        $steps = implode("\n\n", StepsPrompt::content());
        $output = implode("\n", OutputPrompt::content());

        return <<<PROMPT
        {$background}

        === GUIDELINES ===
        {$steps}

        === OUTPUT FORMAT ===
        {$output}
        PROMPT;
    }
}
