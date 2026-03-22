<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\WorkoutPlanStatus;
use App\Jobs\GenerateWorkoutPlanJob;
use App\Models\FitnessInfo;
use App\Models\User;
use App\Models\WorkoutPlan;
use App\Services\WorkoutGenerationService;
use App\Services\WorkoutPlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateWorkoutPlanJobTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<string, mixed> */
    private array $workflowState = [
        'user_id' => 1,
        'user_email' => 'user@example.com',
        'fitness_goals' => 'muscle_gain',
        'schedule' => [
            'training_days_per_week' => 4,
            'available_days' => ['monday', 'tuesday', 'thursday', 'friday'],
            'session_duration' => 60,
        ],
        'equipment' => [
            'items' => ['barbell', 'dumbbells'],
            'gym_access' => true,
        ],
        'constraints' => null,
        'preferences' => [
            'workout_types' => ['strength'],
            'sports' => null,
            'preferred_exercises' => null,
            'additional_notes' => null,
        ],
    ];

    public function test_job_marks_plan_as_processing_then_completed_on_success(): void
    {
        $user = User::factory()->create();
        FitnessInfo::factory()->create(['user_id' => $user->id]);

        $plan = WorkoutPlan::factory()->create([
            'user_id' => $user->id,
            'status' => WorkoutPlanStatus::Pending,
        ]);

        $this->workflowState['user_id'] = $user->id;

        $completedPlan = WorkoutPlan::factory()->make([
            'id' => $plan->id,
            'user_id' => $user->id,
            'status' => WorkoutPlanStatus::Completed,
        ]);

        $this->mock(WorkoutGenerationService::class)
            ->shouldReceive('generate')
            ->once()
            ->andReturn('{"workout_plan":{}}');

        $serviceMock = $this->mock(WorkoutPlanService::class);
        $serviceMock->shouldReceive('fillFromAgentResponse')
            ->once()
            ->with(\Mockery::type(WorkoutPlan::class), \Mockery::type('string'))
            ->andReturn($completedPlan);

        $job = new GenerateWorkoutPlanJob($plan, $user, $this->workflowState);

        app()->call([$job, 'handle']);

        $this->assertDatabaseHas('workout_plans', [
            'id' => $plan->id,
            'status' => WorkoutPlanStatus::Processing->value,
        ]);
    }

    public function test_job_marks_plan_as_failed_when_exception_is_thrown(): void
    {
        $user = User::factory()->create();
        $plan = WorkoutPlan::factory()->create([
            'user_id' => $user->id,
            'status' => WorkoutPlanStatus::Pending,
        ]);

        $job = new GenerateWorkoutPlanJob($plan, $user, $this->workflowState);
        $job->failed(new \RuntimeException('Anthropic API timeout'));

        $this->assertDatabaseHas('workout_plans', [
            'id' => $plan->id,
            'status' => WorkoutPlanStatus::Failed->value,
        ]);
    }

    public function test_job_has_correct_timeout_and_no_retries(): void
    {
        $user = User::factory()->create();
        $plan = WorkoutPlan::factory()->create(['user_id' => $user->id]);

        $job = new GenerateWorkoutPlanJob($plan, $user, $this->workflowState);

        $this->assertSame(600, $job->timeout);
        $this->assertSame(1, $job->tries);
    }
}
