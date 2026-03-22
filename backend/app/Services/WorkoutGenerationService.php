<?php

declare(strict_types=1);

namespace App\Services;

use App\Ai\Agents\FitnessAgent;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WorkoutGenerationService
{
    /**
     * Build the prompt, call Anthropic directly, and return the raw JSON string
     * expected by WorkoutPlanService::fillFromAgentResponse().
     *
     * @param  array<string, mixed>  $data
     */
    public function generate(User $user, array $data): string
    {
        $fitnessData = $this->resolveFitnessData($user, (int) ($data['user_id'] ?? $user->id));
        $fitnessGoal = (string) ($data['fitness_goals'] ?? 'general_fitness');
        $schedule = (array) ($data['schedule'] ?? []);
        $equipment = (array) ($data['equipment'] ?? []);
        $preferences = (array) ($data['preferences'] ?? []);
        $constraints = (string) ($data['constraints'] ?? '');

        $prompt = $this->buildPrompt($fitnessData, $fitnessGoal, $schedule, $equipment, $constraints, $preferences);

        Log::debug('Prompt built for workout plan generation', ['prompt' => $prompt]);

        $response = FitnessAgent::make()->prompt($prompt);

        Log::debug('Output built from Fitness agent', ['text' => $response->text]);

        return $response->text;
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveFitnessData(User $user, int $userId): array
    {
        return Cache::remember(
            "fitness_profile:{$userId}",
            now()->addMinutes(10),
            function () use ($user): array {
                $fitnessInfo = $user->fitnessInfo()->firstOrFail();

                return [
                    'age' => $fitnessInfo->age,
                    'height' => $fitnessInfo->height,
                    'weight' => $fitnessInfo->weight,
                    'gender' => $fitnessInfo->gender,
                    'experience_level' => $fitnessInfo->experience_level,
                ];
            },
        );
    }

    /**
     * @param  array<string, mixed>  $fitnessData
     * @param  array<string, mixed>  $schedule
     * @param  array<string, mixed>  $equipment
     * @param  array<string, mixed>  $preferences
     */
    private function buildPrompt(
        array $fitnessData,
        string $fitnessGoal,
        array $schedule,
        array $equipment,
        string $constraints,
        array $preferences,
    ): string {
        $profileLines = [];

        foreach ($fitnessData as $key => $value) {
            $formatted = is_array($value)
                ? implode(', ', array_map($this->castToString(...), $value))
                : $this->castToString($value);
            $profileLines[] = sprintf('  %s: %s', str_replace('_', ' ', (string) $key), $formatted);
        }

        $sections = [
            '=== USER FITNESS PROFILE ===',
            implode("\n", $profileLines),
        ];

        $requestLines = [];

        if ($fitnessGoal !== '') {
            $requestLines[] = sprintf('  goal: %s', $fitnessGoal);
        }

        if (isset($schedule['training_days_per_week'])) {
            $requestLines[] = sprintf('  training days per week: %d', (int) $schedule['training_days_per_week']);
        }

        if (! empty($schedule['available_days'])) {
            $requestLines[] = sprintf('  available days: %s', implode(', ', (array) $schedule['available_days']));
        }

        if (isset($schedule['session_duration'])) {
            $requestLines[] = sprintf('  session duration: %d minutes', (int) $schedule['session_duration']);
        }

        if (! empty($equipment['items'])) {
            $requestLines[] = sprintf('  equipment: %s', implode(', ', (array) $equipment['items']));
        }

        if (isset($equipment['gym_access'])) {
            $requestLines[] = sprintf('  gym access: %s', $equipment['gym_access'] ? 'yes' : 'no');
        }

        if ($constraints !== '') {
            $requestLines[] = sprintf('  injuries/limitations: %s', $constraints);
        }

        if (! empty($preferences['workout_types'])) {
            $requestLines[] = sprintf('  preferred workout types: %s', implode(', ', (array) $preferences['workout_types']));
        }

        if (! empty($preferences['sports'])) {
            $requestLines[] = sprintf('  sports/activities: %s', (string) $preferences['sports']);
        }

        if (! empty($preferences['preferred_exercises'])) {
            $requestLines[] = sprintf('  preferred exercises: %s', (string) $preferences['preferred_exercises']);
        }

        if (! empty($preferences['additional_notes'])) {
            $requestLines[] = sprintf('  additional notes: %s', (string) $preferences['additional_notes']);
        }

        if ($requestLines !== []) {
            $sections[] = '';
            $sections[] = '=== USER REQUEST ===';
            $sections[] = implode("\n", $requestLines);
        }

        return implode("\n", $sections);
    }

    private function castToString(mixed $value): string
    {
        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        return (string) $value;
    }
}
