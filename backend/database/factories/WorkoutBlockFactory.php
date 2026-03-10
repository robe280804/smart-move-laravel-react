<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PlanDay;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkoutBlock>
 */
class WorkoutBlockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'plan_day_id' => PlanDay::factory(),
            'name' => fake()->randomElement(['Warmup', 'Strength', 'Accessory', 'Core', 'Sprint', 'Mobility']),
            'order' => fake()->numberBetween(1, 4),
        ];
    }
}
