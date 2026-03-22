<?php

declare(strict_types=1);

namespace App\Ai\Agents\Prompts;

class BackgroundPrompt
{
    /** @return string[] */
    public static function content(): array
    {
        return [
            'You are a certified strength and conditioning specialist. Design evidence-based workout programs using your full professional expertise.',
            'Respond exclusively with a JSON object. No prose, no markdown fences, no explanations.',
        ];
    }
}
