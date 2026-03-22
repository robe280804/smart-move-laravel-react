<?php

declare(strict_types=1);

namespace App\Neuron\Nodes;

use App\Neuron\Events\UserInfosCollectedEvent;
use App\Neuron\FitnessAgent;
use App\Neuron\FitnessAgentRag;
use App\Neuron\FitnessAgentRagFiltered;
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

    /**
     * Maps frontend equipment labels to the vocabulary stored in the Qdrant
     * knowledge base (lowercased at ingestion time by WorkflowCsvToQdrant).
     * Keys absent from this map (Bench, everything) are too broad to filter on
     * and are intentionally excluded.
     */
    private const EQUIPMENT_KB_MAP = [
        'Dumbbells' => 'dumbbell',
        'Barbells' => 'barbell',
        'Resistance Bands' => 'resistance band',
        'Pull-up Bar' => 'pull-up bar',
        'Kettlebells' => 'kettlebell',
        'Cable Machine' => 'cable machine',
        'Cardio Equipment' => 'cardio',
        'Bodyweight Only' => 'bodyweight',
    ];

    public function __invoke(UserInfosCollectedEvent $event, WorkflowState $state): StopEvent
    {
        $fitnessData = (array) $state->get('fitness_data', []);
        $fitnessGoals = (array) $state->get('fitness_goals', []);
        $equipment = (array) $state->get('equipment', []);
        $preferences = (array) $state->get('preferences', []);
        $constraints = (string) $state->get('constraints', '');

        $documents = $this->retrieveExercises($fitnessData, $fitnessGoals, $equipment, $preferences, $constraints);

        Log::info('documents retrieval', ['count' => count($documents)]);

        $prompt = $this->buildPrompt(
            $fitnessData,
            $this->formatExerciseContext($documents),
            $fitnessGoals,
            (array) $state->get('schedule', []),
            $equipment,
            $constraints,
            $preferences,
        );

        Log::info('Prompt built for workout plan generation', ['prompt_length' => mb_strlen($prompt)]);

        /** @var WorkoutPlanOutput $output */
        $output = FitnessAgent::make()
            ->structured(new UserMessage($prompt), WorkoutPlanOutput::class);

        Log::info('Workout plan generation completed');

        $state->set('agent_response', json_encode($output));

        return new StopEvent;
    }

    /**
     * Execute up to 5 targeted RAG queries in parallel and return deduplicated documents.
     *
     * Query 1 — goal + experience (+ injury-safe signal when constraints present)
     * Query 2 — equipment + workout type (Qdrant payload filter applied for home users)
     * Query 3 — gym compound / bodyweight calisthenics context
     * Query 4 — sport-specific (only when user provided sports)
     * Query 5 — rehabilitation / injury-safe (only when injury keywords detected)
     *
     * @param  array<string, mixed>  $fitnessData
     * @param  array<int, string>  $fitnessGoals
     * @param  array<string, mixed>  $equipment
     * @param  array<string, mixed>  $preferences
     * @return Document[]
     */
    private function retrieveExercises(
        array $fitnessData,
        array $fitnessGoals,
        array $equipment,
        array $preferences,
        string $constraints,
    ): array {
        $experienceLevel = $this->castToString($fitnessData['experience_level'] ?? '');
        $goals = array_map(fn (string $g): string => str_replace('_', ' ', $g), $fitnessGoals);
        $equipmentItems = (array) ($equipment['items'] ?? []);
        $workoutTypes = (array) ($preferences['workout_types'] ?? []);
        $hasGymAccess = (bool) ($equipment['gym_access'] ?? false);
        $sports = trim((string) ($preferences['sports'] ?? ''));

        // Query 1 — goal + experience + optional injury-safe signal
        $query1 = $this->buildGoalQuery($goals, $experienceLevel, $constraints);

        // Query 3 — gym compound or bodyweight calisthenics
        $query3 = $hasGymAccess
            ? sprintf('%s gym compound isolation exercises resistance training', $experienceLevel)
            : sprintf('%s bodyweight calisthenics home exercises no equipment', $experienceLevel);

        // Equipment filter for Qdrant (applied only when user is home-based)
        $normalizedEquipment = $this->normalizeEquipmentForQuery($equipmentItems);
        $equipmentFilter = ! $hasGymAccess && $normalizedEquipment !== []
            ? $this->buildEquipmentFilter($normalizedEquipment)
            : [];

        /** @var array<int, \Closure(): Document[]> $closures */
        $closures = [
            fn (): array => FitnessAgentRag::make()->resolveRetrieval()->retrieve(new UserMessage($query1)),
        ];

        // Query 2 — equipment + workout type
        if ($equipmentItems !== [] || $workoutTypes !== []) {
            $query2 = implode(' ', array_filter([...$equipmentItems, ...$workoutTypes, 'exercises']));

            if ($equipmentFilter !== []) {
                $closures[] = fn (): array => FitnessAgentRagFiltered::make()
                    ->setEquipmentFilter($equipmentFilter)
                    ->resolveRetrieval()
                    ->retrieve(new UserMessage($query2));
            } else {
                $closures[] = fn (): array => FitnessAgentRag::make()->resolveRetrieval()->retrieve(new UserMessage($query2));
            }
        }

        $closures[] = fn (): array => FitnessAgentRag::make()->resolveRetrieval()->retrieve(new UserMessage($query3));

        // Query 4 — sport-specific (optional)
        if ($sports !== '') {
            $query4 = sprintf('%s sport specific functional exercises training', $sports);
            $closures[] = fn (): array => FitnessAgentRag::make()->resolveRetrieval()->retrieve(new UserMessage($query4));
        }

        // Query 5 — rehabilitation / injury-safe (optional)
        if ($this->isRehabilitationCase($constraints)) {
            $query5 = sprintf('rehabilitation injury safe low impact exercises %s', $constraints);
            $closures[] = fn (): array => FitnessAgentRag::make()->resolveRetrieval()->retrieve(new UserMessage($query5));
        }

        /** @var array<int, Document[]> $batchResults */
        $batchResults = Concurrency::run($closures);

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
     * Build Query 1: goal + experience level, with an injury-safe signal when
     * the user has provided constraints, so Qdrant ranks safer exercise variants higher.
     *
     * @param  string[]  $goals
     */
    private function buildGoalQuery(array $goals, string $experienceLevel, string $constraints): string
    {
        $parts = [...$goals, $experienceLevel, 'workout exercises training'];

        if ($constraints !== '') {
            $parts[] = 'injury safe low impact alternatives';
        }

        return implode(' ', array_filter($parts));
    }

    /**
     * Returns true when the constraint text contains injury or rehabilitation keywords,
     * triggering the optional Query 5 for targeted rehabilitation exercise retrieval.
     */
    private function isRehabilitationCase(string $constraints): bool
    {
        if ($constraints === '') {
            return false;
        }

        $keywords = ['injury', 'pain', 'rehab', 'surgery', 'torn', 'sprain', 'strain', 'fracture', 'recover'];
        $lower = strtolower($constraints);

        foreach ($keywords as $keyword) {
            if (str_contains($lower, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Map frontend equipment labels to the lowercased vocabulary used in the
     * Qdrant knowledge base. Labels absent from EQUIPMENT_KB_MAP are skipped.
     *
     * @param  array<int, string>  $equipmentItems
     * @return array<int, string>
     */
    private function normalizeEquipmentForQuery(array $equipmentItems): array
    {
        $normalized = [];

        foreach ($equipmentItems as $item) {
            if (isset(self::EQUIPMENT_KB_MAP[$item])) {
                $normalized[] = self::EQUIPMENT_KB_MAP[$item];
            }
        }

        return array_values(array_unique($normalized));
    }

    /**
     * Build a Qdrant `should` (OR) filter on the `equipment` payload field.
     * Bodyweight is always appended so exercises that require no equipment
     * remain eligible even when the user owns specialised gear.
     *
     * @param  array<int, string>  $normalizedEquipment
     * @return array<string, mixed>
     */
    private function buildEquipmentFilter(array $normalizedEquipment): array
    {
        $terms = array_values(array_unique([...$normalizedEquipment, 'bodyweight']));

        return [
            'should' => array_map(
                fn (string $term): array => ['key' => 'equipment', 'match' => ['value' => $term]],
                $terms,
            ),
        ];
    }

    /**
     * Format retrieved documents into an exercise context block.
     *
     * @param  Document[]  $documents
     */
    private function formatExerciseContext(array $documents): string
    {
        if ($documents === []) {
            return '';
        }

        $lines = ['=== AVAILABLE EXERCISES FROM KNOWLEDGE BASE ==='];

        foreach ($documents as $document) {
            $lines[] = '- '.$document->getContent();
        }

        return implode("\n", $lines);
    }

    /**
     * Assemble the full prompt for FitnessAgent.
     *
     * @param  array<string, mixed>  $fitnessData
     * @param  array<int, string>  $fitnessGoals
     * @param  array<string, mixed>  $schedule
     * @param  array<string, mixed>  $equipment
     * @param  array<string, mixed>  $preferences
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
