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

    /**
     * Goals that benefit from compound-movement priority in retrieval,
     * prompt guidance, and exercise ordering.
     */
    private const COMPOUND_PRIORITY_GOALS = [
        'strength_building',
        'muscle_gain',
        'athletic_performance',
        'body_recomposition',
    ];

    /**
     * Keywords used to identify compound exercises in retrieved document content
     * for priority sorting.
     */
    private const COMPOUND_EXERCISE_KEYWORDS = [
        'squat',
        'deadlift',
        'bench press',
        'overhead press',
        'barbell row',
        'pull-up',
        'chin-up',
        'lunge',
        'hip thrust',
        'clean',
        'snatch',
        'military press',
        'front squat',
        'romanian deadlift',
        'sumo deadlift',
        'push press',
        'power clean',
        'hang clean',
        'dip',
        'pull up',
        'chin up',
    ];

    public function __invoke(UserInfosCollectedEvent $event, WorkflowState $state): StopEvent
    {
        $fitnessData = (array) $state->get('fitness_data', []);
        $fitnessGoal = (string) $state->get('fitness_goals', 'general_fitness');
        $equipment = (array) $state->get('equipment', []);
        $preferences = (array) $state->get('preferences', []);
        $constraints = (string) $state->get('constraints', '');

        $documents = $this->retrieveExercises($fitnessData, $fitnessGoal, $equipment, $preferences, $constraints);

        $prompt = $this->buildPrompt(
            $fitnessData,
            $this->formatExerciseContext($documents),
            $fitnessGoal,
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
     * Execute up to 6 targeted RAG queries in parallel and return deduplicated documents.
     *
     * Query 1 — goal-specific + experience (+ injury-safe signal when constraints present)
     * Query 2 — equipment + workout type (Qdrant payload filter applied for home users)
     * Query 3 — goal-specific context (gym compound / bodyweight / endurance / etc.)
     * Query 4 — sport-specific (only when user provided sports)
     * Query 5 — rehabilitation / injury-safe (only when injury keywords detected)
     * Query 6 — movement pattern coverage (only for compound-priority goals)
     *
     * @param  array<string, mixed>  $fitnessData
     * @param  array<string, mixed>  $equipment
     * @param  array<string, mixed>  $preferences
     * @return Document[]
     */
    private function retrieveExercises(
        array $fitnessData,
        string $fitnessGoal,
        array $equipment,
        array $preferences,
        string $constraints,
    ): array {
        $experienceLevel = $this->castToString($fitnessData['experience_level'] ?? '');
        $goalFormatted = str_replace('_', ' ', $fitnessGoal);
        $equipmentItems = (array) ($equipment['items'] ?? []);
        $workoutTypes = (array) ($preferences['workout_types'] ?? []);
        $hasGymAccess = (bool) ($equipment['gym_access'] ?? false);
        $sports = trim((string) ($preferences['sports'] ?? ''));
        $primaryGoal = $fitnessGoal;

        // Query 1 — goal-specific + experience + optional injury-safe signal
        $query1 = $this->buildGoalQuery($goalFormatted, $experienceLevel, $constraints, $primaryGoal);

        // Query 3 — goal-specific context query
        $query3 = $this->buildContextQuery($primaryGoal, $experienceLevel, $hasGymAccess);

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

        // Query 6 — movement pattern coverage (compound-priority goals only)
        if ($this->isStrengthOriented($primaryGoal)) {
            $query6 = $this->buildMovementPatternQuery($experienceLevel, $hasGymAccess);
            $closures[] = fn (): array => FitnessAgentRag::make()->resolveRetrieval()->retrieve(new UserMessage($query6));
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

        $documents = $this->prioritizeCompoundExercises($documents, $primaryGoal);

        return array_slice($documents, 0, self::MAX_EXERCISES_IN_CONTEXT);
    }

    /**
     * Build Query 1: goal-specific search query with an injury-safe signal when
     * the user has provided constraints, so Qdrant ranks safer exercise variants higher.
     */
    private function buildGoalQuery(string $goalFormatted, string $experienceLevel, string $constraints, string $primaryGoal): string
    {
        $parts = match (true) {
            $this->isStrengthOriented($primaryGoal) => [
                $goalFormatted,
                'barbell squat deadlift bench press overhead press compound',
                $experienceLevel,
                'exercises',
            ],
            $primaryGoal === 'weight_loss' => [
                $goalFormatted,
                $experienceLevel,
                'high calorie compound circuit metabolic exercises',
            ],
            $primaryGoal === 'endurance' => [
                $goalFormatted,
                $experienceLevel,
                'endurance sustained aerobic conditioning exercises',
            ],
            in_array($primaryGoal, ['flexibility', 'posture_correction', 'rehabilitation'], true) => [
                $goalFormatted,
                $experienceLevel,
                'stretching mobility corrective controlled exercises',
            ],
            default => [$goalFormatted, $experienceLevel, 'workout exercises training'],
        };

        if ($constraints !== '') {
            $parts[] = 'injury safe low impact alternatives';
        }

        return implode(' ', array_filter($parts));
    }

    /**
     * Returns true when the primary goal benefits from compound-movement priority
     * in retrieval, prompt guidance, and exercise ordering.
     */
    private function isStrengthOriented(string $primaryGoal): bool
    {
        return in_array($primaryGoal, self::COMPOUND_PRIORITY_GOALS, true);
    }

    /**
     * Build Query 3: context-specific query tailored to the primary goal and gym access.
     */
    private function buildContextQuery(string $primaryGoal, string $experienceLevel, bool $hasGymAccess): string
    {
        if (! $hasGymAccess) {
            return sprintf('%s bodyweight calisthenics home exercises no equipment', $experienceLevel);
        }

        return match (true) {
            $this->isStrengthOriented($primaryGoal) => sprintf(
                '%s compound multi-joint barbell squat hinge press pull row heavy resistance exercises',
                $experienceLevel,
            ),
            $primaryGoal === 'weight_loss' => sprintf(
                '%s compound full body circuit metabolic conditioning resistance exercises',
                $experienceLevel,
            ),
            $primaryGoal === 'endurance' => sprintf(
                '%s cardio endurance sustained effort aerobic rowing cycling exercises',
                $experienceLevel,
            ),
            default => sprintf('%s gym compound isolation exercises resistance training', $experienceLevel),
        };
    }

    /**
     * Build Query 6: targets fundamental movement patterns to ensure compound exercise
     * coverage for strength-oriented goals.
     */
    private function buildMovementPatternQuery(string $experienceLevel, bool $hasGymAccess): string
    {
        if ($hasGymAccess) {
            return sprintf(
                'hip hinge romanian deadlift barbell row horizontal pull vertical press overhead shoulders %s exercises',
                $experienceLevel,
            );
        }

        return sprintf(
            'pull-up chin-up inverted row pike push-up bodyweight hip hinge single leg %s exercises',
            $experienceLevel,
        );
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
     * Reorder documents so compound exercises appear first for strength-oriented goals.
     * This ensures compounds are prioritized when slicing to MAX_EXERCISES_IN_CONTEXT.
     *
     * @param  Document[]  $documents
     * @return Document[]
     */
    private function prioritizeCompoundExercises(array $documents, string $primaryGoal): array
    {
        if (! $this->isStrengthOriented($primaryGoal)) {
            return $documents;
        }

        $compound = [];
        $other = [];

        foreach ($documents as $document) {
            $content = strtolower($document->getContent());

            if ($this->containsCompoundKeyword($content)) {
                $compound[] = $document;
            } else {
                $other[] = $document;
            }
        }

        return array_merge($compound, $other);
    }

    /**
     * Check if the exercise content contains any compound exercise keyword.
     */
    private function containsCompoundKeyword(string $content): bool
    {
        foreach (self::COMPOUND_EXERCISE_KEYWORDS as $keyword) {
            if (str_contains($content, $keyword)) {
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
     * @param  array<string, mixed>  $schedule
     * @param  array<string, mixed>  $equipment
     * @param  array<string, mixed>  $preferences
     */
    private function buildPrompt(
        array $fitnessData,
        string $exerciseContext,
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
            '',
            sprintf('=== PRIMARY TRAINING GOAL: %s ===', strtoupper(str_replace('_', ' ', $fitnessGoal))),
            $this->buildMethodologySection($fitnessGoal),
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

        if ($exerciseContext !== '') {
            $sections[] = '';
            $sections[] = $exerciseContext;
        }

        if ($this->isSparseInput($constraints, $preferences)) {
            $hasGymAccess = (bool) ($equipment['gym_access'] ?? false);
            $sections[] = '';
            $sections[] = $this->buildSparseInputDefaults($fitnessGoal, $hasGymAccess);
        }

        $sections[] = '';
        $sections[] = 'If the knowledge base does not contain enough suitable exercises for the user profile, select the most appropriate alternatives ONLY from the knowledge base. Do NOT invent new exercises.';

        return implode("\n", $sections);
    }

    /**
     * Returns true when the user provided minimal customization input,
     * indicating the agent should apply professional coaching defaults.
     *
     * @param  array<string, mixed>  $preferences
     */
    private function isSparseInput(string $constraints, array $preferences): bool
    {
        return $constraints === ''
            && empty($preferences['preferred_exercises'])
            && empty($preferences['sports'])
            && empty($preferences['additional_notes']);
    }

    /**
     * Build a goal-specific training methodology section for the prompt.
     * This gives the LLM explicit coaching direction for the stated goal.
     */
    private function buildMethodologySection(string $primaryGoal): string
    {
        return match ($primaryGoal) {
            'strength_building' => 'TRAINING METHODOLOGY: This is a STRENGTH program. Build the plan around heavy compound barbell movements: squat, deadlift, bench press, overhead press, and barbell row. These must form the core of the main training blocks. Accessory and isolation exercises only supplement the main lifts. Program heavy loads (RPE 7-9), low reps (3-6), and long rest periods (2-5 minutes). Prioritize progressive overload on the main compound lifts.',
            'muscle_gain' => 'TRAINING METHODOLOGY: This is a HYPERTROPHY program. Focus on moderate-to-high volume with compound movements as primary exercises and isolation exercises for targeted muscle growth. Use 8-12 rep ranges with controlled tempo. Include exercises that target each muscle group from multiple angles. Ensure sufficient weekly volume per muscle group.',
            'weight_loss' => 'TRAINING METHODOLOGY: This is a FAT LOSS program. Favor high-caloric-expenditure compound exercises and circuit-style training. Combine resistance training with metabolic conditioning. Keep rest periods moderate (45-90s) to maintain elevated heart rate. Include both strength and conditioning elements.',
            'body_recomposition' => 'TRAINING METHODOLOGY: This is a BODY RECOMPOSITION program. Combine strength-focused compound movements with moderate volume to build muscle while losing fat. Use a mix of heavy compounds (RPE 7-9, 6-8 reps) and moderate hypertrophy work (RPE 6-8, 10-12 reps). Rest periods should vary between 60-180s depending on exercise intensity.',
            'endurance' => 'TRAINING METHODOLOGY: This is an ENDURANCE program. Prioritize sustained-effort exercises with progressive duration. Include aerobic capacity builders, higher rep ranges (15+), and exercises that develop muscular endurance. Use shorter rest periods (30-60s) to build cardiovascular conditioning.',
            'flexibility' => 'TRAINING METHODOLOGY: This is a FLEXIBILITY program. Focus on dynamic and static stretching, mobility drills, and controlled-range movements. Include exercises that improve joint range of motion and muscle elasticity. Prioritize muscle groups commonly shortened by sedentary lifestyles.',
            'posture_correction' => 'TRAINING METHODOLOGY: This is a POSTURE CORRECTION program. Focus on strengthening weak posterior chain muscles (upper back, glutes, deep core) and stretching tight anterior muscles (chest, hip flexors). Include corrective exercises, anti-rotation movements, and scapular stabilization drills.',
            'rehabilitation' => 'TRAINING METHODOLOGY: This is a REHABILITATION program. Focus on gentle, controlled movements that restore range of motion and rebuild strength progressively. Avoid high-impact or heavy-load exercises. Include mobility work, stabilization exercises, and gradual loading. Safety is the top priority.',
            'athletic_performance' => 'TRAINING METHODOLOGY: This is an ATHLETIC PERFORMANCE program. Combine power development (Olympic lifts, plyometrics) with sport-specific functional movements. Build explosive strength, agility, and conditioning. Use compound lifts for foundational strength and sport-specific drills for transfer.',
            'functional_fitness' => 'TRAINING METHODOLOGY: This is a FUNCTIONAL FITNESS program. Focus on exercises that improve real-world movement quality: squats, lunges, pushes, pulls, carries, and rotational movements. Emphasize multi-joint, multi-plane exercises that build practical strength and coordination.',
            default => 'TRAINING METHODOLOGY: Design a balanced GENERAL FITNESS program. Include a mix of compound movements, cardiovascular exercises, and flexibility work. Aim for well-rounded fitness across strength, endurance, and mobility domains.',
        };
    }

    /**
     * Build expert coaching defaults for sparse-input scenarios where the user
     * has not specified preferences, injuries, or constraints.
     */
    private function buildSparseInputDefaults(string $primaryGoal, bool $hasGymAccess): string
    {
        $base = 'The user has not specified exercise preferences, injuries, or additional constraints. As a certified strength and conditioning coach, use your professional expertise to select the most effective exercises for their stated goal.';

        if ($this->isStrengthOriented($primaryGoal) && $hasGymAccess) {
            return $base.' For this strength-oriented goal with full gym access, build the program around the fundamental compound lifts: barbell squat, conventional deadlift, bench press, overhead press, and barbell row, with targeted accessory work to address weak points and ensure balanced development.';
        }

        if ($this->isStrengthOriented($primaryGoal)) {
            return $base.' For this strength-oriented goal without gym access, build the program around the most challenging bodyweight progressions: pistol squats, push-up variations, pull-up/chin-up variations, dips, and single-leg hip hinges.';
        }

        return $base.' Select the gold-standard exercises for this goal, prioritizing movements with the highest training transfer and effectiveness.';
    }

    private function castToString(mixed $value): string
    {
        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        return (string) $value;
    }
}
