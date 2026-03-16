<?php

declare(strict_types=1);

namespace App\Neuron\Nodes;

use App\Neuron\Events\UserInfosCollectedEvent;
use App\Neuron\FitnessAgent;
use App\Neuron\FitnessAgentRag;
use App\Neuron\StructuredOutput\WorkoutPlanOutput;
use Illuminate\Support\Facades\Log;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\RAG\Document;
use NeuronAI\Workflow\Events\StopEvent;
use NeuronAI\Workflow\Node;
use NeuronAI\Workflow\WorkflowState;

class GenerateWorkoutPlanNode extends Node
{
    private const TOP_K_EXERCISES = 20;

    public function __invoke(UserInfosCollectedEvent $event, WorkflowState $state): StopEvent
    {
        /** @var array<string, mixed> $fitnessData */
        $fitnessData = (array) $state->get('fitness_data', []);

        $documents = FitnessAgentRag::make()
            ->resolveRetrieval()
            ->retrieve(new UserMessage($this->buildRetrievalQuery($fitnessData)));

        Log::info('documents retrivial', [
            'doc' => $documents
        ]);

        $prompt = $this->buildPrompt(
            $fitnessData,
            $this->formatExerciseContext($documents),
            (array) $state->get('fitness_goals', []),
            (array) $state->get('schedule', []),
            (array) $state->get('equipment', []),
            (string) $state->get('constraints', ''),
            (array) $state->get('preferences', []),
        );

        Log::info('prompt in Generate workout plan node', [
            'prompt' => $prompt
        ]);

        /** @var WorkoutPlanOutput $output */
        $output = FitnessAgent::make()
            ->structured(new UserMessage($prompt), WorkoutPlanOutput::class);

        Log::info('response in Generate workout plan node', [
            'response' => $output
        ]);

        $state->set('agent_response', json_encode($output));

        return new StopEvent();
    }

    /**
     * Build a retrieval query from the collected fitness profile.
     *
     * @param array<string, mixed> $fitnessData
     */
    private function buildRetrievalQuery(array $fitnessData): string
    {
        $parts = array_filter([
            $this->castToString($fitnessData['experience_level'] ?? null),
            $this->castToString($fitnessData['gender'] ?? null),
            $this->castToString($fitnessData['height'] ?? null),
            $this->castToString($fitnessData['weight'] ?? null),
            $this->castToString($fitnessData['age'] ?? null),
            'fitness workout exercises',
        ]);

        return implode(' ', $parts);
    }

    /**
     * Format retrieved documents into an exercise context block.
     *
     * @param Document[] $documents
     */
    private function formatExerciseContext(array $documents): string
    {
        if ($documents === []) {
            return '';
        }

        $lines = ['=== AVAILABLE EXERCISES FROM KNOWLEDGE BASE ==='];

        foreach (array_slice($documents, 0, self::TOP_K_EXERCISES) as $document) {
            $lines[] = '- ' . $document->getContent();
        }

        return implode("\n", $lines);
    }

    /**
     * Assemble the full prompt for FitnessAgent.
     *
     * @param array<string, mixed>  $fitnessData
     * @param array<int, string>    $fitnessGoals
     * @param array<string, mixed>  $schedule
     * @param array<string, mixed>  $equipment
     * @param array<string, mixed>  $preferences
     */
    private function buildPrompt(
        array $fitnessData,
        string $exerciseContext,
        array $fitnessGoals,
        array $schedule,
        array $equipment,
        string $constraints,
        array $preferences,
    ): string {
        $profileLines = [];

        foreach ($fitnessData as $key => $value) {
            $formatted = is_array($value) ? implode(', ', array_map($this->castToString(...), $value)) : $this->castToString($value);
            $profileLines[] = sprintf('  %s: %s', str_replace('_', ' ', (string) $key), $formatted);
        }

        $sections = [
            '=== USER FITNESS PROFILE ===',
            implode("\n", $profileLines),
        ];

        $requestLines = [];

        if ($fitnessGoals !== []) {
            $requestLines[] = sprintf('  goals: %s', implode(', ', $fitnessGoals));
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

        if ($exerciseContext !== '') {
            $sections[] = '';
            $sections[] = $exerciseContext;
        }

        $sections[] = '';
        $sections[] = 'If the knowledge base does not contain enough suitable exercises for the user profile, select the most appropriate alternatives ONLY from the knowledge base. Do NOT invent new exercises.';
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
