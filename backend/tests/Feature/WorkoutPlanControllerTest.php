<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TokenAbility;
use App\Models\BlockExercise;
use App\Models\Exercise;
use App\Models\PlanDay;
use App\Models\User;
use App\Models\WorkoutBlock;
use App\Models\WorkoutPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WorkoutPlanControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(User $user): static
    {
        Sanctum::actingAs($user, [TokenAbility::ACCESS_API->value]);

        return $this;
    }

    private function createPlanWithFullHierarchy(User $user): WorkoutPlan
    {
        $plan = WorkoutPlan::factory()->create(['user_id' => $user->id]);
        $day = PlanDay::factory()->create(['workout_plan_id' => $plan->id]);
        $block = WorkoutBlock::factory()->create(['plan_day_id' => $day->id]);
        $exercise = Exercise::factory()->create();
        BlockExercise::factory()->create([
            'workout_block_id' => $block->id,
            'exercise_id' => $exercise->id,
        ]);

        return $plan;
    }

    // ==================== INDEX ====================

    public function test_authenticated_user_can_retrieve_their_workout_plans(): void
    {
        $user = User::factory()->create();
        WorkoutPlan::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAsUser($user)->getJson('/api/v1/workout-plans');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_index_returns_empty_array_when_user_has_no_plans(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->getJson('/api/v1/workout-plans');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_index_only_returns_plans_belonging_to_authenticated_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        WorkoutPlan::factory()->count(2)->create(['user_id' => $user->id]);
        WorkoutPlan::factory()->count(3)->create(['user_id' => $otherUser->id]);

        $response = $this->actingAsUser($user)->getJson('/api/v1/workout-plans');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_index_response_includes_plan_days_with_nested_relations(): void
    {
        $user = User::factory()->create();
        $this->createPlanWithFullHierarchy($user);

        $response = $this->actingAsUser($user)->getJson('/api/v1/workout-plans');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'training_days_per_week',
                        'goal',
                        'experience_level',
                        'workout_type',
                        'plan_days' => [
                            '*' => [
                                'id',
                                'day_of_week',
                                'workout_blocks' => [
                                    '*' => [
                                        'id',
                                        'name',
                                        'order',
                                        'block_exercises' => [
                                            '*' => [
                                                'id',
                                                'sets',
                                                'reps',
                                                'exercise' => ['id', 'name', 'category'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]);
    }

    public function test_unauthenticated_user_cannot_list_workout_plans(): void
    {
        $response = $this->getJson('/api/v1/workout-plans');

        $response->assertUnauthorized();
    }

    public function test_index_filters_plans_older_than_30_days_for_free_plan(): void
    {
        $user = User::factory()->create();

        // Plan created 45 days ago (should be hidden for free plan)
        WorkoutPlan::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subDays(45),
        ]);

        // Plan created today (should be visible)
        WorkoutPlan::factory()->create(['user_id' => $user->id]);

        // Free plan user (no subscription) - SubscriptionService returns Free with 30-day limit
        $response = $this->actingAsUser($user)->getJson('/api/v1/workout-plans');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    // ==================== SHOW ====================

    public function test_owner_can_view_own_workout_plan(): void
    {
        $user = User::factory()->create();
        $plan = $this->createPlanWithFullHierarchy($user);

        $response = $this->actingAsUser($user)->getJson("/api/v1/workout-plans/{$plan->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $plan->id)
            ->assertJsonPath('data.user_id', $user->id);
    }

    public function test_show_response_includes_full_nested_hierarchy(): void
    {
        $user = User::factory()->create();
        $plan = $this->createPlanWithFullHierarchy($user);

        $response = $this->actingAsUser($user)->getJson("/api/v1/workout-plans/{$plan->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'training_days_per_week',
                    'goal',
                    'experience_level',
                    'workout_type',
                    'plan_days' => [
                        '*' => [
                            'id',
                            'day_of_week',
                            'workout_blocks' => [
                                '*' => [
                                    'id',
                                    'name',
                                    'order',
                                    'block_exercises' => [
                                        '*' => [
                                            'id',
                                            'exercise' => ['id', 'name', 'category'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]);
    }

    public function test_user_cannot_view_another_users_workout_plan(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $plan = WorkoutPlan::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAsUser($otherUser)->getJson("/api/v1/workout-plans/{$plan->id}");

        $response->assertForbidden();
    }

    public function test_show_returns_not_found_for_nonexistent_plan(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->getJson('/api/v1/workout-plans/99999');

        $response->assertNotFound();
    }

    public function test_unauthenticated_user_cannot_view_workout_plan(): void
    {
        $plan = WorkoutPlan::factory()->create();

        $response = $this->getJson("/api/v1/workout-plans/{$plan->id}");

        $response->assertUnauthorized();
    }

    // ==================== DESTROY ====================

    public function test_owner_can_delete_own_workout_plan(): void
    {
        $user = User::factory()->create();
        $plan = WorkoutPlan::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAsUser($user)->deleteJson("/api/v1/workout-plans/{$plan->id}");

        $response->assertNoContent();
    }

    public function test_delete_removes_workout_plan_from_database(): void
    {
        $user = User::factory()->create();
        $plan = WorkoutPlan::factory()->create(['user_id' => $user->id]);

        $this->actingAsUser($user)->deleteJson("/api/v1/workout-plans/{$plan->id}");

        $this->assertDatabaseMissing('workout_plans', ['id' => $plan->id]);
    }

    public function test_delete_cascades_to_plan_days_and_blocks(): void
    {
        $user = User::factory()->create();
        $plan = $this->createPlanWithFullHierarchy($user);
        $dayId = $plan->planDays()->first()->id;
        $blockId = $plan->planDays()->first()->workoutBlocks()->first()->id;

        $this->actingAsUser($user)->deleteJson("/api/v1/workout-plans/{$plan->id}");

        $this->assertDatabaseMissing('plan_days', ['id' => $dayId]);
        $this->assertDatabaseMissing('workout_blocks', ['id' => $blockId]);
    }

    public function test_user_cannot_delete_another_users_workout_plan(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $plan = WorkoutPlan::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAsUser($otherUser)->deleteJson("/api/v1/workout-plans/{$plan->id}");

        $response->assertForbidden();
    }

    public function test_delete_returns_not_found_for_nonexistent_plan(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->deleteJson('/api/v1/workout-plans/99999');

        $response->assertNotFound();
    }

    public function test_unauthenticated_user_cannot_delete_workout_plan(): void
    {
        $plan = WorkoutPlan::factory()->create();

        $response = $this->deleteJson("/api/v1/workout-plans/{$plan->id}");

        $response->assertUnauthorized();
    }
}
