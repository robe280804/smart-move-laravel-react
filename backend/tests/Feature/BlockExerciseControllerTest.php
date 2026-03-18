<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\SubscriptionPlan;
use App\Enums\TokenAbility;
use App\Models\BlockExercise;
use App\Models\Exercise;
use App\Models\PlanDay;
use App\Models\User;
use App\Models\WorkoutBlock;
use App\Models\WorkoutPlan;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BlockExerciseControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(User $user): static
    {
        Sanctum::actingAs($user, [TokenAbility::ACCESS_API->value]);

        return $this;
    }

    /** @return array{0: WorkoutPlan, 1: BlockExercise} */
    private function createBlockExerciseForUser(User $user): array
    {
        $plan = WorkoutPlan::factory()->create(['user_id' => $user->id]);
        $day = PlanDay::factory()->create(['workout_plan_id' => $plan->id]);
        $block = WorkoutBlock::factory()->create(['plan_day_id' => $day->id]);
        $exercise = Exercise::factory()->create();
        $blockExercise = BlockExercise::factory()->create([
            'workout_block_id' => $block->id,
            'exercise_id' => $exercise->id,
            'sets' => 3,
            'reps' => 10,
        ]);

        return [$plan, $blockExercise];
    }

    public function test_owner_with_advanced_plan_can_update_exercise(): void
    {
        $user = User::factory()->create();
        [$plan, $blockExercise] = $this->createBlockExerciseForUser($user);

        $this->mock(SubscriptionService::class)
            ->shouldReceive('getPlan')
            ->andReturn(SubscriptionPlan::Advanced);

        $response = $this->actingAsUser($user)->patchJson(
            "/api/v1/workout-plans/{$plan->id}/exercises/{$blockExercise->id}",
            ['sets' => 4, 'reps' => 12]
        );

        $response->assertOk()
            ->assertJsonPath('data.sets', 4)
            ->assertJsonPath('data.reps', 12);
    }

    public function test_free_plan_user_cannot_update_exercise(): void
    {
        $user = User::factory()->create();
        [$plan, $blockExercise] = $this->createBlockExerciseForUser($user);

        $this->mock(SubscriptionService::class)
            ->shouldReceive('getPlan')
            ->andReturn(SubscriptionPlan::Free);

        $response = $this->actingAsUser($user)->patchJson(
            "/api/v1/workout-plans/{$plan->id}/exercises/{$blockExercise->id}",
            ['sets' => 4]
        );

        $response->assertForbidden();
    }

    public function test_user_cannot_update_another_users_exercise(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        [$plan, $blockExercise] = $this->createBlockExerciseForUser($owner);

        $this->mock(SubscriptionService::class)
            ->shouldReceive('getPlan')
            ->andReturn(SubscriptionPlan::Advanced);

        $response = $this->actingAsUser($otherUser)->patchJson(
            "/api/v1/workout-plans/{$plan->id}/exercises/{$blockExercise->id}",
            ['sets' => 4]
        );

        $response->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_update_exercise(): void
    {
        $user = User::factory()->create();
        [$plan, $blockExercise] = $this->createBlockExerciseForUser($user);

        $response = $this->patchJson(
            "/api/v1/workout-plans/{$plan->id}/exercises/{$blockExercise->id}",
            ['sets' => 4]
        );

        $response->assertUnauthorized();
    }

    public function test_returns_404_when_exercise_does_not_belong_to_plan(): void
    {
        $user = User::factory()->create();
        [$plan] = $this->createBlockExerciseForUser($user);

        // Create a blockExercise for a different plan
        $otherPlan = WorkoutPlan::factory()->create(['user_id' => $user->id]);
        $day = PlanDay::factory()->create(['workout_plan_id' => $otherPlan->id]);
        $block = WorkoutBlock::factory()->create(['plan_day_id' => $day->id]);
        $exercise = Exercise::factory()->create();
        $blockExercise = BlockExercise::factory()->create([
            'workout_block_id' => $block->id,
            'exercise_id' => $exercise->id,
        ]);

        $this->mock(SubscriptionService::class)
            ->shouldReceive('getPlan')
            ->andReturn(SubscriptionPlan::Advanced);

        $response = $this->actingAsUser($user)->patchJson(
            "/api/v1/workout-plans/{$plan->id}/exercises/{$blockExercise->id}",
            ['sets' => 4]
        );

        $response->assertNotFound();
    }
}
