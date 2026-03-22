<?php

declare(strict_types=1);

namespace App\Neuron\Prompts;

class StepsPrompt
{
    public static function content(): array
    {
        return [
            'STEP 1 — PROFILE ANALYSIS: Read the complete user fitness profile provided in the message. Extract: physical data (age, weight, height, gender), fitness goals, training schedule, available equipment, injuries or limitations, and preferences. Do not ask for any additional information — everything you need is already provided.',
            'STEP 2 — EXERCISE SELECTION: Select exercises exclusively from the AVAILABLE EXERCISES FROM KNOWLEDGE BASE section provided in the message. Do not invent exercise names or use exercises not listed. Choose exercises that match the user\'s available equipment, experience level, and training goal. Apply these goal-specific selection priorities:'.
                ' — strength_building / muscle_gain / athletic_performance: Build the plan around multi-joint compound movements (squat pattern, hip hinge, horizontal push, horizontal pull, vertical push, vertical pull). At least 60% of main-block exercises must be compound. Isolation exercises serve only as accessories to the main lifts.'.
                ' — weight_loss / body_recomposition: Favor high-caloric-expenditure compound exercises and circuit-style movements. Combine resistance and metabolic conditioning exercises.'.
                ' — endurance: Prioritize sustained-effort exercises with progressive duration and aerobic capacity builders.'.
                ' — flexibility / posture_correction / rehabilitation: Focus on stretching, mobility drills, corrective exercises, and controlled-range movements.'.
                ' — general_fitness / functional_fitness: Balanced mix of compound, bodyweight, and functional exercises across all movement patterns.'.
                ' Ensure balanced movement-pattern coverage across the training week for strength-based goals: squat/knee-dominant, hip-hinge/posterior-chain, horizontal push, horizontal pull, vertical push, vertical pull. Every pattern must appear at least once per week.',
            'STEP 3 — PLAN DESIGN: Design the workout plan following these evidence-based principles:'.
                ' (1) Apply progressive overload appropriate to the experience level;'.
                ' (2) Balance training volume and intensity — do not overload any single session;'.
                ' (3) Every training day must have a Warmup block (order 1) and a Cool-down block (last order);'.
                ' (4) Use these goal-specific programming parameters:'.
                ' — strength_building: 3–5 sets, 3–6 reps, 120–300s rest, RPE 7–9;'.
                ' — muscle_gain: 3–4 sets, 8–12 reps, 60–120s rest, RPE 6–8;'.
                ' — endurance: 2–3 sets, 15+ reps or timed, 30–60s rest, RPE 5–7;'.
                ' — weight_loss / body_recomposition: 3–4 sets, 10–15 reps, 45–90s rest, RPE 6–8;'.
                ' — flexibility / rehabilitation: 2–3 sets, timed holds 20–60s, 30–45s rest, RPE 3–5;'.
                ' — athletic_performance: 3–5 sets, 3–8 reps for power / 8–12 for accessory, 90–180s rest, RPE 7–9;'.
                ' — general_fitness / functional_fitness: 3 sets, 10–15 reps, 60–90s rest, RPE 5–7;'.
                ' (5) Respect rest days and do not schedule training on rest days;'.
                ' (6) For beginners, prefer compound bodyweight or machine-guided exercises over free weights;'.
                ' (7) In main training blocks, list compound exercises before isolation/accessory exercises.',
            'STEP 4 — SAFETY CHECK: Before emitting the JSON, verify:'.
                ' (1) High-risk compound lifts (e.g. barbell squat, deadlift, Olympic lifts) are assigned only to intermediate, advanced, or professional users;'.
                ' (2) Exercises involving the injured or limited body part are excluded or replaced with safe alternatives;'.
                ' (3) Total weekly volume is sustainable and does not risk overtraining for the stated experience level.',
            'STEP 5 — JSON EMISSION: Emit the complete plan as a single, valid JSON object in one message. Never split the JSON across multiple messages. Never emit partial JSON. Include the exercise name field for every exercise.',
        ];
    }
}
