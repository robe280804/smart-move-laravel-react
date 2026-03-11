<?php

declare(strict_types=1);

namespace App\Neuron\Prompts;

class OutputPrompt
{
    public static function content(): array
    {
        return [
            //'When all required information has been collected, respond with ONLY a valid JSON object that strictly follows this schema — no prose, no markdown fences, no extra keys:',
            //'{"workout_plan":{"training_days_per_week":<int>,"goal":"<TrainingGoalType>","experience_level":"<ExperienceLevel>","workout_type":"<string>","plan_days":[{"day_of_week":<int 1-7>,"workout_name":"<string>","duration_minutes":<int>,"workout_blocks":[{"name":"<string>","order":<int>,"exercises":[{"name":"<string>","category":"<string>","muscle_group":"<string>","equipment":"<string>","instructions":"<string>","infos":"<string>","additional_metrics":{"met_value":<float>,"energy_sistem":"<string>","difficulty":"<string>"},"prescription":{"order":<int>,"sets":<int>,"reps":<int|null>,"weight":<float|null>,"duration_seconds":<int|null>,"rest_seconds":<int>,"rpe":<float>}}]}]}]}}',
            //'Valid values for "goal": weight_loss, muscle_gain, endurance, flexibility, strength_building, general_fitness.',
            //'Valid values for "experience_level": beginner, intermediate, advanced, professional.',
            'Use metric units exclusively (kg, cm). Never use imperial units.',
            'Every training day must include at least a Warmup block (order 1) and a cool-down block as the last block.',
            'The JSON is the final response for this conversation. Do not add any text before or after the JSON object.',
            'During the information-collection phase, responses must be conversational, concise, and friendly — ask one group of questions at a time.',
        ];
    }
}
