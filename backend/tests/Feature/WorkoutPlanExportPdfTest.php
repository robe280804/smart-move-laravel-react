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

class WorkoutPlanExportPdfTest extends TestCase
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

    // ==================== SUBSCRIPTION GATING ====================

    public function test_advanced_plan_user_can_export_pdf(): void
    {
        $user = User::factory()->create();
        $plan = $this->createPlanWithFullHierarchy($user);

        $this->mock(SubscriptionService::class)
            ->shouldReceive('getPlan')
            ->andReturn(SubscriptionPlan::Advanced);

        $response = $this->actingAsUser($user)->getJson("/api/v1/workout-plans/{$plan->id}/pdf");

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_pro_plan_user_can_export_pdf(): void
    {
        $user = User::factory()->create();
        $plan = $this->createPlanWithFullHierarchy($user);

        $this->mock(SubscriptionService::class)
            ->shouldReceive('getPlan')
            ->andReturn(SubscriptionPlan::Pro);

        $response = $this->actingAsUser($user)->getJson("/api/v1/workout-plans/{$plan->id}/pdf");

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_free_plan_user_cannot_export_pdf(): void
    {
        $user = User::factory()->create();
        $plan = $this->createPlanWithFullHierarchy($user);

        $this->mock(SubscriptionService::class)
            ->shouldReceive('getPlan')
            ->andReturn(SubscriptionPlan::Free);

        $response = $this->actingAsUser($user)->getJson("/api/v1/workout-plans/{$plan->id}/pdf");

        $response->assertForbidden();
    }

    // ==================== AUTHORIZATION ====================

    public function test_user_cannot_export_another_users_plan_pdf(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $plan = $this->createPlanWithFullHierarchy($owner);

        $this->mock(SubscriptionService::class)
            ->shouldReceive('getPlan')
            ->andReturn(SubscriptionPlan::Pro);

        $response = $this->actingAsUser($otherUser)->getJson("/api/v1/workout-plans/{$plan->id}/pdf");

        $response->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_export_pdf(): void
    {
        $user = User::factory()->create();
        $plan = $this->createPlanWithFullHierarchy($user);

        $response = $this->getJson("/api/v1/workout-plans/{$plan->id}/pdf");

        $response->assertUnauthorized();
    }

    // ==================== EDGE CASES ====================

    public function test_export_pdf_returns_not_found_for_nonexistent_plan(): void
    {
        $user = User::factory()->create();

        $this->mock(SubscriptionService::class)
            ->shouldReceive('getPlan')
            ->andReturn(SubscriptionPlan::Pro);

        $response = $this->actingAsUser($user)->getJson('/api/v1/workout-plans/99999/pdf');

        $response->assertNotFound();
    }

    public function test_pdf_response_has_download_content_disposition(): void
    {
        $user = User::factory()->create();
        $plan = $this->createPlanWithFullHierarchy($user);

        $this->mock(SubscriptionService::class)
            ->shouldReceive('getPlan')
            ->andReturn(SubscriptionPlan::Advanced);

        $response = $this->actingAsUser($user)->getJson("/api/v1/workout-plans/{$plan->id}/pdf");

        $response->assertOk();
        $contentDisposition = $response->headers->get('content-disposition');
        $this->assertStringContainsString('attachment', $contentDisposition);
        $this->assertStringContainsString("workout-plan-{$plan->id}.pdf", $contentDisposition);
    }
}
