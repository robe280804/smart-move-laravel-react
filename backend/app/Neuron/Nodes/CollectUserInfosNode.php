<?php

declare(strict_types=1);

namespace App\Neuron\Nodes;

use App\Models\User;
use App\Neuron\Events\SanitizeInputEvent;
use App\Neuron\Events\UserInfosCollectedEvent;
use App\Repositories\Contracts\FitnessInfoRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use NeuronAI\Workflow\Interrupt\Action;
use NeuronAI\Workflow\Interrupt\ApprovalRequest;
use NeuronAI\Workflow\Node;
use NeuronAI\Workflow\Events\StopEvent;
use NeuronAI\Workflow\WorkflowState;

class CollectUserInfosNode extends Node
{
    private const  MAX_FIELD_ATTEMPTS = 3;

    /** @var array<string, string> */
    private const  REQUIRED_FIELDS = [
        'height' => 'What is your height in centimetres? (e.g. 175)',
        'weight' => 'What is your weight in kilograms? (e.g. 70)',
        'age' => 'How old are you?',
        'gender' => 'What is your gender? (male / female)',
        'experience_level' => 'What is your fitness experience level? (beginner / intermediate / advanced / professional)',
    ];

    public function __construct(
        private readonly FitnessInfoRepositoryInterface $repository,
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(SanitizeInputEvent $event, WorkflowState $state): StopEvent|UserInfosCollectedEvent
    {
        // Step 1 — ask once whether the user wants to use their saved profile.
        // WorkflowState persists across resumes, so we skip this on subsequent runs.
        if (! $state->has('use_saved_profile')) {
            $request = new ApprovalRequest(
                'Would you like to use your saved fitness profile to generate the workout plan?',
                [new Action('use_saved_profile', 'Use my saved profile', 'Approve to use your saved data, reject to enter data manually')]
            );

            /** @var ApprovalRequest $resumed */
            $resumed = $this->interrupt($request);

            $action = $resumed->getAction('use_saved_profile');
            $state->set('use_saved_profile', $action !== null && $action->isApproved());
        }

        // Step 2 — seed fitness data from the database once.
        if (! $state->has('fitness_data')) {
            $state->set('fitness_data', $this->loadProfileFromDatabase($state, (bool) $state->get('use_saved_profile', false)));
        }

        // Step 3 — collect every field that is still missing, with up to MAX_FIELD_ATTEMPTS retries.
        foreach (self::REQUIRED_FIELDS as $field => $baseQuestion) {
            /** @var array<string, mixed> $fitnessData */
            $fitnessData = $state->get('fitness_data', []);

            if (! empty($fitnessData[$field])) {
                continue;
            }

            $failedAttempts = (int) $state->get("field_{$field}_failed_attempts", 0);
            $question = $failedAttempts > 0
                ? "Invalid input, please try again (attempt " . ($failedAttempts + 1) . " of " . self::MAX_FIELD_ATTEMPTS . "). {$baseQuestion}"
                : $baseQuestion;

            /** @var ApprovalRequest $resumed */
            $resumed = $this->interrupt(
                new ApprovalRequest($question, [new Action("provide_{$field}", "Provide {$field}", $baseQuestion)])
            );

            $raw = trim((string) ($resumed->getAction("provide_{$field}")?->feedback ?? ''));

            if ($this->validateField($field, $raw)) {
                $fitnessData[$field] = $raw;
                $state->set('fitness_data', $fitnessData);
                // Valid: continue the loop; the next missing field will interrupt on its own.
            } else {
                $newFailedAttempts = $failedAttempts + 1;
                $state->set("field_{$field}_failed_attempts", $newFailedAttempts);

                if ($newFailedAttempts >= self::MAX_FIELD_ATTEMPTS) {
                    $state->set(
                        'collection_error',
                        "Could not collect valid data for '{$field}' after " . self::MAX_FIELD_ATTEMPTS . " attempts. Please restart the process."
                    );

                    return new StopEvent();
                }

                // Re-interrupt to ask the same field again on the next resume.
                $retryQuestion = "Invalid input, please try again (attempt " . ($newFailedAttempts + 1) . " of " . self::MAX_FIELD_ATTEMPTS . "). {$baseQuestion}";

                $this->interrupt(
                    new ApprovalRequest($retryQuestion, [new Action("provide_{$field}", "Provide {$field}", $baseQuestion)])
                );
            }
        }

        return new UserInfosCollectedEvent();
    }



    private function validateField(string $field, string $value): bool
    {
        return match ($field) {
            'height' => is_numeric($value) && (float) $value >= 50 && (float) $value <= 300,
            'weight' => is_numeric($value) && (float) $value >= 20 && (float) $value <= 500,
            'age'    => ctype_digit($value) && (int) $value >= 10 && (int) $value <= 120,
            'gender' => in_array(strtolower($value), ['male', 'female'], true),
            'experience_level' => in_array(strtolower($value), ['beginner', 'intermediate', 'advanced', 'professional'], true),
            default  => false,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function loadProfileFromDatabase(WorkflowState $state, bool $useSavedProfile): array
    {
        if (! $useSavedProfile) {
            return [];
        }

        $userId = $state->get('user_id');
        $user = $userId !== null ? $this->userRepository->findById((int) $userId) : null;

        if (! $user instanceof User) {
            return [];
        }

        $fitnessInfo = $this->repository->findByUser($user);

        if ($fitnessInfo === null) {
            return [];
        }

        return array_filter([
            'height'           => $fitnessInfo->height,
            'weight'           => $fitnessInfo->weight,
            'age'              => $fitnessInfo->age,
            'gender'           => $fitnessInfo->gender?->value,
            'experience_level' => $fitnessInfo->experience_level?->value,
        ], fn(mixed $value): bool => $value !== null && $value !== '');
    }
}
