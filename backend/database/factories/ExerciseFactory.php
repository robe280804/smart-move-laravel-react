<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Exercise>
 */
class ExerciseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Barbell Back Squat', 'Romanian Deadlift', 'Bench Press', 'Pull-Up',
                'Overhead Press', 'Barbell Row', 'Dumbbell Lunge', 'Push-Up', 'Plank',
            ]),
            'category' => fake()->randomElement(['strength', 'sprint', 'mobility', 'conditioning']),
            'muscle_group' => fake()->optional()->randomElement([
                'chest', 'back', 'legs', 'shoulders', 'arms', 'core',
            ]),
            'equipment' => fake()->optional()->randomElement([
                'barbell', 'dumbbell', 'kettlebell', 'bodyweight', 'machine',
            ]),
            'instructions' => fake()->optional()->sentence(),
            'infos' => fake()->optional()->paragraph(),
            'additional_metrics' => fake()->boolean() ? [
                'calories_burned_per_minute' => fake()->randomFloat(1, 3, 15),
                'met_value' => fake()->randomFloat(1, 2, 12),
            ] : null,
        ];
    }
}
