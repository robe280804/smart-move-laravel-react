<?php

declare(strict_types=1);

namespace App\Services;

use App\Ai\Agents\StructuredOutput\WorkoutPlanOutput;
use App\Enums\ExperienceLevel;
use App\Enums\TrainingGoalType;
use App\Enums\WorkoutPlanStatus;
use App\Enums\WorkoutType;
use App\Models\BlockExercise;
use App\Models\Exercise;
use App\Models\User;
use App\Models\WorkoutPlan;
use App\Repositories\Contracts\WorkoutPlanRepositoryInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class WorkoutPlanService
{
    public function __construct(
        private readonly WorkoutPlanRepositoryInterface $workoutPlanRepository,
    ) {}

    /** @return Collection<int, WorkoutPlan> */
    public function getAll(User $user, ?Carbon $since = null): Collection
    {
        return $this->workoutPlanRepository->findByUserWithRelationsSince($user, $since);
    }

    public function updateBlockExercise(WorkoutPlan $workoutPlan, BlockExercise $blockExercise, array $data): BlockExercise
    {
        $blockExercise->load('workoutBlock.planDay');

        if ($blockExercise->workoutBlock->planDay->workout_plan_id !== $workoutPlan->id) {
            throw new ModelNotFoundException;
        }

        $blockExercise->update($data);

        return $blockExercise->fresh();
    }

    public function loadRelations(WorkoutPlan $workoutPlan): WorkoutPlan
    {
        return $workoutPlan->load('planDays.workoutBlocks.blockExercises.exercise');
    }

    public function generatePdf(WorkoutPlan $workoutPlan): Response
    {
        $this->loadRelations($workoutPlan);

        $dayNames = [
            1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday',
            5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday',
        ];

        $goalEnum = $workoutPlan->goal;
        $workoutTypeEnum = WorkoutType::tryFrom($workoutPlan->workout_type);
        $experienceEnum = $workoutPlan->experience_level;

        $totalExercises = $workoutPlan->planDays->sum(
            fn ($day) => $day->workoutBlocks->sum(fn ($block) => $block->blockExercises->count()),
        );

        $trainingDays = $workoutPlan->planDays
            ->sortBy('day_of_week')
            ->map(fn ($day) => substr($dayNames[$day->day_of_week] ?? '?', 0, 3))
            ->implode(' · ');

        $pdf = Pdf::loadView('pdf.workout-plan', [
            'plan' => $workoutPlan,
            'goalLabel' => $goalEnum instanceof TrainingGoalType ? $goalEnum->label() : ucfirst((string) $workoutPlan->goal),
            'workoutTypeLabel' => $workoutTypeEnum?->label() ?? ucfirst($workoutPlan->workout_type),
            'experienceLabel' => $experienceEnum instanceof ExperienceLevel ? ucfirst($experienceEnum->value) : ucfirst((string) $workoutPlan->experience_level),
            'totalExercises' => $totalExercises,
            'trainingDays' => $trainingDays,
            'dayNames' => $dayNames,
            'generatedDate' => $workoutPlan->created_at->format('M d, Y'),
        ]);

        $pdf->setPaper('A4');

        $fileName = 'workout-plan-'.$workoutPlan->id.'.pdf';

        return $pdf->download($fileName);
    }

    public function delete(WorkoutPlan $workoutPlan): void
    {
        $this->workoutPlanRepository->delete($workoutPlan);
    }

    /**
     * Create a placeholder plan that the async job will populate once the agent finishes.
     *
     * @param  array<string, mixed>|null  $generationRequest
     */
    public function createPending(User $user, ?array $generationRequest = null): WorkoutPlan
    {
        return $this->workoutPlanRepository->create([
            'user_id' => $user->id,
            'status' => WorkoutPlanStatus::Pending,
            'generation_request' => $generationRequest,
        ]);
    }

    /**
     * Populate an existing plan with the agent JSON response and mark it as completed.
     * Called from GenerateWorkoutPlanJob after the workflow finishes.
     */
    public function fillFromAgentResponse(WorkoutPlan $plan, string $jsonResponse): WorkoutPlan
    {
        $output = $this->parseJsonResponse($jsonResponse);

        return DB::transaction(function () use ($output, $plan): WorkoutPlan {
            $planData = $output->workout_plan;

            $plan->update([
                'training_days_per_week' => $planData->training_days_per_week,
                'goal' => $planData->goal,
                'experience_level' => $planData->experience_level,
                'workout_type' => $planData->workout_type,
                'status' => WorkoutPlanStatus::Completed,
            ]);

            foreach ($planData->plan_days as $dayData) {
                $planDay = $plan->planDays()->create([
                    'day_of_week' => $dayData->day_of_week,
                    'workout_name' => $dayData->workout_name,
                    'duration_minutes' => $dayData->duration_minutes,
                ]);

                foreach ($dayData->workout_blocks as $blockData) {
                    $block = $planDay->workoutBlocks()->create([
                        'name' => $blockData->name,
                        'order' => $blockData->order,
                    ]);

                    foreach ($blockData->exercises as $exerciseData) {
                        $exercise = Exercise::query()->create([
                            'name' => $exerciseData->name,
                            'category' => $exerciseData->category,
                            'muscle_group' => $exerciseData->muscle_group,
                            'equipment' => $exerciseData->equipment,
                            'instructions' => $exerciseData->instructions,
                            'infos' => $exerciseData->infos,
                            'additional_metrics' => $exerciseData->additional_metrics?->toArray(),
                        ]);

                        $prescription = $exerciseData->prescription;

                        $block->blockExercises()->create([
                            'exercise_id' => $exercise->id,
                            'order' => $prescription->order,
                            'sets' => $prescription->sets,
                            'reps' => $prescription->reps,
                            'weight' => $prescription->weight,
                            'duration_seconds' => $prescription->duration_seconds,
                            'rest_seconds' => $prescription->rest_seconds,
                            'rpe' => $prescription->rpe,
                        ]);
                    }
                }
            }

            return $plan->load('planDays.workoutBlocks.blockExercises.exercise');
        });
    }

    private function parseJsonResponse(string $response): WorkoutPlanOutput
    {
        $cleaned = preg_replace('/^```(?:json)?\s*/m', '', $response);
        $cleaned = preg_replace('/\s*```\s*$/m', '', (string) $cleaned);
        $cleaned = trim((string) $cleaned);

        $data = json_decode($cleaned, true);

        Log::info('Agent response parsed', ['has_workout_plan' => isset($data['workout_plan'])]);

        if (! is_array($data)) {
            throw new RuntimeException('Agent response is not valid JSON.');
        }

        if (! isset($data['workout_plan'])) {
            throw new RuntimeException('Agent response is missing the "workout_plan" key.');
        }

        return WorkoutPlanOutput::fromArray($data);
    }
}
