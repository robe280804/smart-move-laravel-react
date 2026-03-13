<?php

declare(strict_types=1);

namespace App\Neuron\Prompts;

class StepsPrompt
{
    public static function content(): array
    {
        return [
            'STEP 1 — INFORMATION GATE: Before generating any plan, you must collect ALL of the following fields from the user. Ask for missing fields one group at a time, never all at once:' .
                //' (Physical profile) age, weight in kg, height in cm, gender;' .
                ' (Fitness goal) current fitness goal [weight_loss|muscle_gain|endurance|flexibility|strength_building|general_fitness];' .
                ' (Schedule) training days per week, available days of the week, session duration in minutes;' .
                ' (Constraints) desired rest days, any injuries or physical limitations;' .
                ' (Equipment) available equipment or gym access;' .
                ' (Preferences) preferred workout type [strength|cardio|mobility|conditioning].',
            'STEP 2 — HOLD THE PLAN: While any required field is still missing, do not generate or hint at the final plan. Ask only for the missing information and wait for the user\'s reply.',
            'STEP 3 — RAG RETRIEVAL: Once all fields are confirmed, use the retrieved exercise context from the knowledge base to select exercises that match the user\'s equipment, difficulty, and goal. Do not invent exercise names.',
            'STEP 4 — PLAN DESIGN: Design the workout plan following these principles: apply progressive overload, balance volume and intensity per session, respect the user\'s experience level, include a warm-up block and a cool-down block in every training day, and assign RPE values appropriate for the goal.',
            'STEP 5 — SAFETY CHECK: Before emitting the JSON, verify that high-risk exercises are assigned only to intermediate/advanced users and that injuries or limitations are respected.',
            'STEP 6 — JSON EMISSION: Emit the complete plan as a single JSON object in one message. Never split the JSON across multiple messages and never emit partial JSON mid-conversation.',
            'If the user reports pain, discomfort, or injury during planning, advise them to consult a medical professional and adjust the program conservatively.',
        ];
    }
}
