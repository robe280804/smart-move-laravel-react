<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

class UserDataExportService
{
    /**
     * Collect all personal data for a user in a portable format (GDPR Article 20).
     *
     * @return array<string, mixed>
     */
    public function export(User $user): array
    {
        $user->load([
            'fitnessInfo',
            'workoutPlans.planDays.workoutBlocks.blockExercises.exercise',
            'feedbacks',
        ]);

        return [
            'exported_at' => now()->toISOString(),
            'profile' => $this->profile($user),
            'fitness_info' => $this->fitnessInfo($user),
            'workout_plans' => $this->workoutPlans($user),
            'feedback' => $this->feedback($user),
        ];
    }

    /** @return array<string, mixed> */
    private function profile(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'surname' => $user->surname,
            'email' => $user->email,
            'role' => $user->getRoleNames()->first(),
            'email_verified_at' => $user->email_verified_at?->toISOString(),
            'account_created_at' => $user->created_at->toISOString(),
        ];
    }

    /** @return array<string, mixed>|null */
    private function fitnessInfo(User $user): ?array
    {
        $info = $user->fitnessInfo;

        if ($info === null) {
            return null;
        }

        return [
            'height_cm' => $info->height,
            'weight_kg' => $info->weight,
            'age' => $info->age,
            'gender' => $info->gender->value,
            'experience_level' => $info->experience_level->value,
            'recorded_at' => $info->created_at->toISOString(),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function workoutPlans(User $user): array
    {
        return $user->workoutPlans->map(function ($plan) {
            return [
                'id' => $plan->id,
                'status' => $plan->status->value,
                'goal' => $plan->goal->value,
                'experience_level' => $plan->experience_level->value,
                'training_days_per_week' => $plan->training_days_per_week,
                'created_at' => $plan->created_at->toISOString(),
                'days' => $plan->planDays->map(function ($day) {
                    return [
                        'day_of_week' => $day->day_of_week,
                        'workout_name' => $day->workout_name,
                        'duration_minutes' => $day->duration_minutes,
                        'blocks' => $day->workoutBlocks->map(function ($block) {
                            return [
                                'name' => $block->name,
                                'order' => $block->order,
                                'exercises' => $block->blockExercises->map(function ($be) {
                                    return [
                                        'exercise' => $be->exercise->name,
                                        'category' => $be->exercise->category,
                                        'muscle_group' => $be->exercise->muscle_group,
                                        'sets' => $be->sets,
                                        'reps' => $be->reps,
                                        'weight_kg' => $be->weight,
                                        'duration_seconds' => $be->duration_seconds,
                                        'rest_seconds' => $be->rest_seconds,
                                        'rpe' => $be->rpe,
                                    ];
                                })->values()->all(),
                            ];
                        })->values()->all(),
                    ];
                })->values()->all(),
            ];
        })->values()->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function feedback(User $user): array
    {
        return $user->feedbacks->map(fn ($fb) => [
            'rating' => $fb->rating,
            'message' => $fb->message,
            'submitted_at' => $fb->created_at->toISOString(),
        ])->values()->all();
    }
}
