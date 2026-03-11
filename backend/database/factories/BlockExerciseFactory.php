<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\WorkoutBlock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BlockExercise>
 */
class BlockExerciseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workout_block_id' => WorkoutBlock::factory(),
            'exercise_id' => Exercise::factory(),
            'order' => fake()->optional()->numberBetween(1, 10),
            'sets' => fake()->optional()->numberBetween(1, 5),
            'reps' => fake()->optional()->numberBetween(5, 20),
            'weight' => fake()->optional()->randomFloat(2, 0, 200),
            'duration_seconds' => fake()->optional()->numberBetween(10, 300),
            'rest_seconds' => fake()->optional()->numberBetween(30, 180),
            'rpe' => fake()->optional()->randomFloat(1, 6, 10),
        ];
    }
}
