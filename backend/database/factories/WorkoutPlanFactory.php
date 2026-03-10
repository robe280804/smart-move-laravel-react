<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ExperienceLevel;
use App\Enums\TrainingGoalType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkoutPlan>
 */
class WorkoutPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'training_days_per_week' => fake()->numberBetween(2, 6),
            'goal' => fake()->randomElement(TrainingGoalType::cases())->value,
            'experience_level' => fake()->randomElement(ExperienceLevel::cases())->value,
            'workout_type' => fake()->randomElement(['strength', 'sprint', 'mobility', 'conditioning', 'rest']),
        ];
    }
}
