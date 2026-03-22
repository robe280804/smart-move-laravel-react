<?php

declare(strict_types=1);

namespace App\Ai\Agents\Prompts;

class StepsPrompt
{
    /** @return string[] */
    public static function content(): array
    {
        return [
            'Read the user fitness profile provided in the message. All necessary information is included — do not request clarification. Design a workout plan appropriate for the user\'s goal, experience level, physical data, available equipment, schedule, and any stated injuries or limitations.',
            'Emit the complete plan as a single valid JSON object. Never split across messages. Never emit partial JSON. Include the "name" field for every exercise.',
        ];
    }
}
