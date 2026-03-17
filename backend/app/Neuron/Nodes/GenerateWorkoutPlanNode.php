<?php

declare(strict_types=1);

namespace App\Neuron\Nodes;

use App\Neuron\Events\UserInfosCollectedEvent;
use App\Neuron\FitnessAgent;
use App\Neuron\FitnessAgentRag;
use App\Neuron\StructuredOutput\WorkoutPlanOutput;
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Facades\Log;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\RAG\Document;
use NeuronAI\Workflow\Events\StopEvent;
use NeuronAI\Workflow\Node;
use NeuronAI\Workflow\WorkflowState;

class GenerateWorkoutPlanNode extends Node
{
    private const MAX_EXERCISES_IN_CONTEXT = 30;

    public function __invoke(UserInfosCollectedEvent $event, WorkflowState $state): StopEvent
    {
        // Set the state 
        $fitnessData = (array) $state->get('fitness_data', []);
        $fitnessGoals = (array) $state->get('fitness_goals', []);
        $equipment = (array) $state->get('equipment', []);
        $preferences = (array) $state->get('preferences', []);

        // Load the relevant document 
        $documents = $this->retrieveExercises($fitnessData, $fitnessGoals, $equipment, $preferences);

        Log::info('documents retrieval', ['count' => count($documents)]);

        // Build prompt
        $prompt = $this->buildPrompt(
            $fitnessData,
            $this->formatExerciseContext($documents),
            $fitnessGoals,
            (array) $state->get('schedule', []),
            $equipment,
            (string) $state->get('constraints', ''),
            $preferences,
        );

        Log::info('prompt in Generate workout plan node', ['prompt' => $prompt]);

        /** @var WorkoutPlanOutput $output */
        $output = FitnessAgent::make()
            ->structured(new UserMessage($prompt), WorkoutPlanOutput::class);

        Log::info('response in Generate workout plan node', ['response' => $output]);

        $state->set('agent_response', json_encode($output));

        return new StopEvent();
    }

    /**
     * Execute multiple targeted RAG queries and return deduplicated documents.
     * Using three queries with distinct semantic angles (goal, equipment, gym context)
     * ensures the retrieved set covers all workout plan needs rather than clustering
     * around a single topic.
     *
     * @param array<string, mixed> $fitnessData
     * @param array<int, string>   $fitnessGoals
     * @param array<string, mixed> $equipment
     * @param array<string, mixed> $preferences
     * @return Document[]
     */
    private function retrieveExercises(
        array $fitnessData,
        array $fitnessGoals,
        array $equipment,
        array $preferences,
    ): array {
        // Build 3 queries
        $queries = $this->buildRetrievalQueries($fitnessData, $fitnessGoals, $equipment, $preferences);

        // Run each query separated on concurrency
        /** @var array<int, Document[]> $batchResults */
        $batchResults = Concurrency::run(
            array_map(
                fn(string $query) => fn(): array => FitnessAgentRag::make()->resolveRetrieval()->retrieve(new UserMessage($query)),
                $queries,
            ),
        );

        $seen = [];
        $documents = [];

        foreach ($batchResults as $results) {
            foreach ($results as $document) {
                $key = md5($document->getContent());
                if (! isset($seen[$key])) {
                    $seen[$key] = true;
                    $documents[] = $document;
                }
            }
        }

        return array_slice($documents, 0, self::MAX_EXERCISES_IN_CONTEXT);
    }

    /**
     * Build three focused retrieval queries to maximise semantic coverage.
     *
     * Query 1 — goal + experience: highest semantic relevance for exercise selection.
     * Query 2 — equipment + workout type: ensures only equipment-appropriate exercises are included.
     * Query 3 — gym/bodyweight context: fills compound/isolation or calisthenics gaps.
     *
     * @param array<string, mixed> $fitnessData
     * @param array<int, string>   $fitnessGoals
     * @param array<string, mixed> $equipment
     * @param array<string, mixed> $preferences
     * @return string[]
     */
    private function buildRetrievalQueries(
        array $fitnessData,
        array $fitnessGoals,
        array $equipment,
        array $preferences,
    ): array {
        $experienceLevel = $this->castToString($fitnessData['experience_level'] ?? '');
        $goals = array_map(fn(string $g) => str_replace('_', ' ', $g), $fitnessGoals);
        $equipmentItems = (array) ($equipment['items'] ?? []);
        $workoutTypes = (array) ($preferences['workout_types'] ?? []);
        $hasGymAccess = (bool) ($equipment['gym_access'] ?? false);

        $queries = [];

        $queries[] = implode(' ', array_filter([...$goals, $experienceLevel, 'workout exercises training']));

        if ($equipmentItems !== [] || $workoutTypes !== []) {
            $queries[] = implode(' ', array_filter([...$equipmentItems, ...$workoutTypes, 'exercises']));
        }

        $queries[] = $hasGymAccess
            ? sprintf('%s gym compound isolation exercises resistance training', $experienceLevel)
            : sprintf('%s bodyweight calisthenics home exercises no equipment', $experienceLevel);

        return array_values(array_filter($queries));
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

        foreach ($documents as $document) {
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
