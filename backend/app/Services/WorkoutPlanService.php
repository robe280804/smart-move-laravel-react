<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\WorkoutPlanStatus;
use App\Models\Exercise;
use App\Models\User;
use App\Models\WorkoutPlan;
use App\Repositories\Contracts\WorkoutPlanRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class WorkoutPlanService
{
    public function __construct(
        private readonly WorkoutPlanRepositoryInterface $workoutPlanRepository,
    ) {}

    /** @return Collection<int, WorkoutPlan> */
    public function getAll(User $user): Collection
    {
        return $this->workoutPlanRepository->findByUserWithRelations($user);
    }

    public function loadRelations(WorkoutPlan $workoutPlan): WorkoutPlan
    {
        return $workoutPlan->load('planDays.workoutBlocks.blockExercises.exercise');
    }

    public function delete(WorkoutPlan $workoutPlan): void
    {
        $this->workoutPlanRepository->delete($workoutPlan);
    }

    /**
     * Create a placeholder plan that the async job will populate once the agent finishes.
     */
    public function createPending(User $user): WorkoutPlan
    {
        return $this->workoutPlanRepository->create([
            'user_id' => $user->id,
            'status'  => WorkoutPlanStatus::Pending,
        ]);
    }

    /**
     * Populate an existing plan with the agent JSON response and mark it as completed.
     * Called from GenerateWorkoutPlanJob after the workflow finishes.
     */
    public function fillFromAgentResponse(WorkoutPlan $plan, string $jsonResponse): WorkoutPlan
    {
        $data = $this->parseJsonResponse($jsonResponse);

        return DB::transaction(function () use ($data, $plan): WorkoutPlan {
            $planData = $data['workout_plan'];

            $plan->update([
                'training_days_per_week' => $planData['training_days_per_week'],
                'goal'                   => $planData['goal'],
                'experience_level'       => $planData['experience_level'],
                'workout_type'           => $planData['workout_type'],
                'status'                 => WorkoutPlanStatus::Completed,
            ]);

            foreach ($planData['plan_days'] as $dayData) {
                $planDay = $plan->planDays()->create([
                    'day_of_week'      => $dayData['day_of_week'],
                    'workout_name'     => $dayData['workout_name'] ?? null,
                    'duration_minutes' => $dayData['duration_minutes'],
                ]);

                foreach ($dayData['workout_blocks'] as $blockData) {
                    $block = $planDay->workoutBlocks()->create([
                        'name'  => $blockData['name'],
                        'order' => $blockData['order'],
                    ]);

                    foreach ($blockData['exercises'] as $exerciseData) {
                        $exercise = Exercise::query()->create([
                            'name'               => $exerciseData['name'] ?? null,
                            'category'           => $exerciseData['category'],
                            'muscle_group'       => $exerciseData['muscle_group'] ?? null,
                            'equipment'          => $exerciseData['equipment'] ?? null,
                            'instructions'       => $exerciseData['instructions'] ?? null,
                            'infos'              => $exerciseData['infos'] ?? null,
                            'additional_metrics' => $exerciseData['additional_metrics'] ?? null,
                        ]);

                        $prescription = $exerciseData['prescription'] ?? [];

                        $block->blockExercises()->create([
                            'exercise_id'      => $exercise->id,
                            'order'            => $prescription['order'] ?? null,
                            'sets'             => $prescription['sets'] ?? null,
                            'reps'             => $prescription['reps'] ?? null,
                            'weight'           => $prescription['weight'] ?? null,
                            'duration_seconds' => $prescription['duration_seconds'] ?? null,
                            'rest_seconds'     => $prescription['rest_seconds'] ?? null,
                            'rpe'              => $prescription['rpe'] ?? null,
                        ]);
                    }
                }
            }

            return $plan->load('planDays.workoutBlocks.blockExercises.exercise');
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function parseJsonResponse(string $response): array
    {
        $cleaned = preg_replace('/^```(?:json)?\s*/m', '', $response);
        $cleaned = preg_replace('/\s*```\s*$/m', '', (string) $cleaned);
        $cleaned = trim((string) $cleaned);

        $data = json_decode($cleaned, true);

        Log::info('agent response', ['res' => $data]);

        if (! is_array($data)) {
            throw new RuntimeException('Agent response is not valid JSON.');
        }

        if (! isset($data['workout_plan'])) {
            throw new RuntimeException('Agent response is missing the "workout_plan" key.');
        }

        return $data;
    }
}
