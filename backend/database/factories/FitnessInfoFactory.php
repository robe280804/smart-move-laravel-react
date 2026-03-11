<?php

namespace Database\Factories;

use App\Enums\ExperienceLevel;
use App\Enums\Gender;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FitnessInfo>
 */
class FitnessInfoFactory extends Factory
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
            'height' => fake()->randomFloat(2, 150, 210),
            'weight' => fake()->randomFloat(2, 45, 150),
            'age' => fake()->numberBetween(16, 80),
            'gender' => fake()->randomElement(Gender::cases())->value,
            'experience_level' => fake()->randomElement(ExperienceLevel::cases())->value,
        ];
    }

    /**
     * State with only height and weight populated; age, gender and experience_level are null.
     * Used to test that the agent asks for only the missing fields.
     */
    public function partial(): static
    {
        return $this->state(fn(array $attributes) => [
            'age' => null,
            'gender' => null,
            'experience_level' => null,
        ]);
    }
}
