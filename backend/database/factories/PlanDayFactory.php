<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\WorkoutPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlanDay>
 */
class PlanDayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workout_plan_id' => WorkoutPlan::factory(),
            'day_of_week' => fake()->numberBetween(1, 7),
            'workout_name' => fake()->optional()->randomElement([
                'Strength', 'Back', 'Pull', 'Leg', 'Sprint Max Effort', 'Mobility', 'Core',
            ]),
            'duration_minutes' => fake()->numberBetween(30, 90),
        ];
    }
}
