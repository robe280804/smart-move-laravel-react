<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\WorkoutPlanStatus;
use App\Models\WorkoutPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeoutStalePlansCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_marks_pending_plans_older_than_ten_minutes_as_failed(): void
    {
        $stalePlan = WorkoutPlan::factory()->create([
            'status' => WorkoutPlanStatus::Pending,
            'created_at' => now()->subMinutes(15),
        ]);

        $this->artisan('plans:timeout-stale')->assertSuccessful();

        $stalePlan->refresh();
        $this->assertSame(WorkoutPlanStatus::Failed, $stalePlan->status);
        $this->assertSame('generation_timeout', $stalePlan->failure_reason);
    }

    public function test_marks_processing_plans_older_than_ten_minutes_as_failed(): void
    {
        $stalePlan = WorkoutPlan::factory()->create([
            'status' => WorkoutPlanStatus::Processing,
            'created_at' => now()->subMinutes(12),
        ]);

        $this->artisan('plans:timeout-stale')->assertSuccessful();

        $stalePlan->refresh();
        $this->assertSame(WorkoutPlanStatus::Failed, $stalePlan->status);
        $this->assertSame('generation_timeout', $stalePlan->failure_reason);
    }

    public function test_does_not_touch_recent_pending_plans(): void
    {
        $recentPlan = WorkoutPlan::factory()->create([
            'status' => WorkoutPlanStatus::Pending,
            'created_at' => now()->subMinutes(3),
        ]);

        $this->artisan('plans:timeout-stale')->assertSuccessful();

        $recentPlan->refresh();
        $this->assertSame(WorkoutPlanStatus::Pending, $recentPlan->status);
        $this->assertNull($recentPlan->failure_reason);
    }

    public function test_does_not_touch_completed_plans(): void
    {
        $completedPlan = WorkoutPlan::factory()->create([
            'status' => WorkoutPlanStatus::Completed,
            'created_at' => now()->subMinutes(60),
        ]);

        $this->artisan('plans:timeout-stale')->assertSuccessful();

        $completedPlan->refresh();
        $this->assertSame(WorkoutPlanStatus::Completed, $completedPlan->status);
    }

    public function test_does_not_touch_already_failed_plans(): void
    {
        $failedPlan = WorkoutPlan::factory()->create([
            'status' => WorkoutPlanStatus::Failed,
            'failure_reason' => 'generation_error',
            'created_at' => now()->subMinutes(60),
        ]);

        $this->artisan('plans:timeout-stale')->assertSuccessful();

        $failedPlan->refresh();
        $this->assertSame('generation_error', $failedPlan->failure_reason);
    }

    public function test_outputs_count_of_timed_out_plans(): void
    {
        WorkoutPlan::factory()->create([
            'status' => WorkoutPlanStatus::Pending,
            'created_at' => now()->subMinutes(20),
        ]);
        WorkoutPlan::factory()->create([
            'status' => WorkoutPlanStatus::Processing,
            'created_at' => now()->subMinutes(15),
        ]);

        $this->artisan('plans:timeout-stale')
            ->expectsOutputToContain('Timed out 2 stale workout plan(s)')
            ->assertSuccessful();
    }
}
