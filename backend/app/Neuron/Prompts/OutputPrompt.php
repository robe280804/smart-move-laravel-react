<?php

declare(strict_types=1);

namespace App\Neuron\Prompts;

class OutputPrompt
{
    public static function content(): array
    {
        return [
            'Respond with ONLY a valid JSON object — no prose, no markdown fences, no extra keys.',
            '{"workout_plan":{"training_days_per_week":<int>,"goal":"<TrainingGoalType>","experience_level":"<ExperienceLevel>","workout_type":"<WorkoutType>","plan_days":[{"day_of_week":<int 1-7>,"workout_name":"<string>","duration_minutes":<int>,"workout_blocks":[{"name":"<string>","order":<int>,"exercises":[{"name":"<string>","category":"<string>","muscle_group":"<string>","equipment":"<string>","instructions":"<string>","infos":"<string>","additional_metrics":{"description":"<string>","met_value":<float>,"energy_system":"<string>","difficulty":"<string>"},"prescription":{"order":<int>,"sets":<int>,"reps":<int|null>,"weight":<float|null>,"duration_seconds":<int|null>,"rest_seconds":<int>,"rpe":<float 1-10>}}]}]}]}}',
            'Valid values for "goal": weight_loss, muscle_gain, strength_building, endurance, flexibility, general_fitness, body_recomposition, athletic_performance, rehabilitation, posture_correction, functional_fitness.',
            'Valid values for "experience_level": beginner, intermediate, advanced, professional.',
            'Valid values for "workout_type": strength, cardio, mobility, conditioning, hiit, bodyweight, functional, core, recovery.',
            'Valid values for "category": compound, isolation, cardio, mobility, plyometric, core, conditioning.',
            'When the user selects multiple workout types, set "workout_type" to the type most aligned with the primary training goal. Reflect all selected types in the actual exercise programming across the plan days.',
            'Valid values for "energy_system": aerobic, anaerobic_lactic, anaerobic_alactic, mixed.',
            'Valid values for "difficulty": beginner, intermediate, advanced, professional.',
            'Use metric units exclusively (kg, cm). Never use imperial units.',
            'Every training day must include at least a Warmup block (order 1) and a Cool-down block as the last block.',
            'Every exercise object must include the "name" field with the exact name from the knowledge base.',
        ];
    }
}
