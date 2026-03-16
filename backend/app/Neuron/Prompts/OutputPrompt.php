<?php

declare(strict_types=1);

namespace App\Neuron\Prompts;

class OutputPrompt
{
    public static function content(): array
    {
        return [
            'Respond with ONLY a valid JSON object — no prose, no markdown fences, no extra keys.',
            '{"workout_plan":{"training_days_per_week":<int>,"goal":"<TrainingGoalType>","experience_level":"<ExperienceLevel>","workout_type":"<string>","plan_days":[{"day_of_week":<int 1-7>,"workout_name":"<string>","duration_minutes":<int>,"workout_blocks":[{"name":"<string>","order":<int>,"exercises":[{"category":"<string>","muscle_group":"<string>","equipment":"<string>","instructions":"<string>","infos":"<string>","additional_metrics":{"met_value":<float>,"energy_system":"<string>","difficulty":"<string>"},"prescription":{"order":<int>,"sets":<int>,"reps":<int|null>,"weight":<float|null>,"duration_seconds":<int|null>,"rest_seconds":<int>,"rpe":<float>}}]}]}]}}',
            'Valid values for "goal": weight_loss, muscle_gain, endurance, flexibility, strength_building, general_fitness.',
            'Valid values for "experience_level": beginner, intermediate, advanced, professional.',
            'Use metric units exclusively (kg, cm). Never use imperial units.',
            'Every training day must include at least a Warmup block (order 1) and a cool-down block as the last block.',
        ];
    }
}
