<?php

declare(strict_types=1);

namespace App\Neuron\Prompts;

class StepsPrompt
{
    public static function content(): array
    {
        return [
            'STEP 1 — PROFILE ANALYSIS: Read the complete user fitness profile provided in the message. Extract: physical data (age, weight, height, gender), fitness goals, training schedule, available equipment, injuries or limitations, and preferences. Do not ask for any additional information — everything you need is already provided.',
            'STEP 2 — EXERCISE SELECTION: Select exercises exclusively from the AVAILABLE EXERCISES FROM KNOWLEDGE BASE section provided in the message. Do not invent exercise names or use exercises not listed. Choose exercises that match the user\'s available equipment, experience level, and training goal.',
            'STEP 3 — PLAN DESIGN: Design the workout plan following these evidence-based principles:' .
                ' (1) Apply progressive overload appropriate to the experience level;' .
                ' (2) Balance training volume and intensity — do not overload any single session;' .
                ' (3) Every training day must have a Warmup block (order 1) and a Cool-down block (last order);' .
                ' (4) Assign RPE values calibrated to the goal (e.g. strength: RPE 7–9, fat loss: RPE 6–8, endurance: RPE 5–7);' .
                ' (5) Respect rest days and do not schedule training on rest days;' .
                ' (6) For beginners, prefer compound bodyweight or machine-guided exercises over free weights.',
            'STEP 4 — SAFETY CHECK: Before emitting the JSON, verify:' .
                ' (1) High-risk compound lifts (e.g. barbell squat, deadlift, Olympic lifts) are assigned only to intermediate, advanced, or professional users;' .
                ' (2) Exercises involving the injured or limited body part are excluded or replaced with safe alternatives;' .
                ' (3) Total weekly volume is sustainable and does not risk overtraining for the stated experience level.',
            'STEP 5 — JSON EMISSION: Emit the complete plan as a single, valid JSON object in one message. Never split the JSON across multiple messages. Never emit partial JSON. Include the exercise name field for every exercise.',
        ];
    }
}
