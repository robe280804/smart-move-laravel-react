<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\SubscriptionPlan;
use App\Enums\TokenAbility;
use App\Enums\WorkoutPlanStatus;
use App\Jobs\GenerateWorkoutPlanJob;
use App\Models\FitnessInfo;
use App\Models\User;
use App\Models\WorkoutPlan;
use App\Services\SubscriptionService;
use App\Services\WorkoutPlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AgentControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<string, mixed> */
    private array $validRequestData = [
        'fitness_goals' => ['muscle_gain'],
        'training_days_per_week' => 4,
        'available_days' => ['Monday', 'Tuesday', 'Thursday', 'Friday'],
        'session_duration' => 60,
        'equipment' => ['Barbells', 'Dumbbells'],
        'gym_access' => true,
        'workout_type' => ['strength'],
    ];

    private function actingAsUser(User $user): static
    {
        Sanctum::actingAs($user, [TokenAbility::ACCESS_API->value]);

        return $this;
    }

    // ==================== AUTHENTICATION ====================

    public function test_unauthenticated_user_cannot_generate_workout(): void
    {
        $response = $this->postJson('/api/v1/agent/generate-workout', $this->validRequestData);

        $response->assertUnauthorized();
    }

    // ==================== VALIDATION ====================

    public function test_returns_422_when_fitness_goals_is_missing(): void
    {
        $user = User::factory()->create();
        $data = $this->validRequestData;
        unset($data['fitness_goals']);

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent/generate-workout', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['fitness_goals']);
    }

    public function test_returns_422_when_fitness_goals_exceeds_max(): void
    {
        $user = User::factory()->create();
        $data = array_merge($this->validRequestData, ['fitness_goals' => ['a', 'b', 'c', 'd']]);

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent/generate-workout', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['fitness_goals']);
    }

    public function test_returns_422_when_training_days_per_week_is_missing(): void
    {
        $user = User::factory()->create();
        $data = $this->validRequestData;
        unset($data['training_days_per_week']);

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent/generate-workout', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['training_days_per_week']);
    }

    public function test_returns_422_when_training_days_per_week_exceeds_max(): void
    {
        $user = User::factory()->create();
        $data = array_merge($this->validRequestData, ['training_days_per_week' => 8]);

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent/generate-workout', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['training_days_per_week']);
    }

    public function test_returns_422_when_available_days_is_missing(): void
    {
        $user = User::factory()->create();
        $data = $this->validRequestData;
        unset($data['available_days']);

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent/generate-workout', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['available_days']);
    }

    public function test_returns_422_when_session_duration_is_missing(): void
    {
        $user = User::factory()->create();
        $data = $this->validRequestData;
        unset($data['session_duration']);

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent/generate-workout', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['session_duration']);
    }

    public function test_returns_422_when_session_duration_is_below_minimum(): void
    {
        $user = User::factory()->create();
        $data = array_merge($this->validRequestData, ['session_duration' => 10]);

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent/generate-workout', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['session_duration']);
    }

    public function test_returns_422_when_equipment_is_missing(): void
    {
        $user = User::factory()->create();
        $data = $this->validRequestData;
        unset($data['equipment']);

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent/generate-workout', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['equipment']);
    }

    public function test_returns_422_when_gym_access_is_missing(): void
    {
        $user = User::factory()->create();
        $data = $this->validRequestData;
        unset($data['gym_access']);

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent/generate-workout', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['gym_access']);
    }

    public function test_returns_422_when_workout_type_is_missing(): void
    {
        $user = User::factory()->create();
        $data = $this->validRequestData;
        unset($data['workout_type']);

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent/generate-workout', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['workout_type']);
    }

    // ==================== HAPPY PATH ====================

    public function test_returns_202_with_pending_plan_and_dispatches_job(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        FitnessInfo::factory()->create(['user_id' => $user->id]);
        $plan = WorkoutPlan::factory()->make(['id' => 1, 'user_id' => $user->id, 'status' => WorkoutPlanStatus::Pending]);
        $plan->setRelation('planDays', collect());

        $this->mock(WorkoutPlanService::class)
            ->shouldReceive('createPending')
            ->once()
            ->with(\Mockery::type(User::class))
            ->andReturn($plan);

        $this->mock(SubscriptionService::class)
            ->shouldReceive('canGenerate')->andReturn(true)
            ->shouldReceive('canSaveActivePlan')->andReturn(true)
            ->shouldReceive('getPlan')->andReturn(SubscriptionPlan::Free);

        $this->actingAsUser($user)->postJson('/api/v1/agent/generate-workout', $this->validRequestData)
            ->assertStatus(202)
            ->assertJsonStructure([
                'data' => ['id', 'user_id', 'status', 'plan_days'],
            ])
            ->assertJsonPath('data.status', WorkoutPlanStatus::Pending->value);

        Queue::assertPushed(GenerateWorkoutPlanJob::class);
    }

    public function test_accepts_optional_nullable_fields(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        FitnessInfo::factory()->create(['user_id' => $user->id]);
        $plan = WorkoutPlan::factory()->make(['id' => 1, 'user_id' => $user->id, 'status' => WorkoutPlanStatus::Pending]);
        $plan->setRelation('planDays', collect());

        $this->mock(WorkoutPlanService::class)
            ->shouldReceive('createPending')
            ->once()
            ->andReturn($plan);

        $this->mock(SubscriptionService::class)
            ->shouldReceive('canGenerate')->andReturn(true)
            ->shouldReceive('canSaveActivePlan')->andReturn(true)
            ->shouldReceive('getPlan')->andReturn(SubscriptionPlan::Free);

        $data = array_merge($this->validRequestData, [
            'injuries' => 'left knee pain',
            'sports' => 'cycling',
            'preferred_exercises' => 'squats, deadlifts',
            'additional_notes' => 'prefer morning sessions',
        ]);

        $this->actingAsUser($user)->postJson('/api/v1/agent/generate-workout', $data)
            ->assertStatus(202);

        Queue::assertPushed(GenerateWorkoutPlanJob::class);
    }

    public function test_job_is_dispatched_with_correct_state_payload(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        FitnessInfo::factory()->create(['user_id' => $user->id]);
        $plan = WorkoutPlan::factory()->make(['id' => 1, 'user_id' => $user->id, 'status' => WorkoutPlanStatus::Pending]);
        $plan->setRelation('planDays', collect());

        $this->mock(WorkoutPlanService::class)
            ->shouldReceive('createPending')
            ->once()
            ->andReturn($plan);

        $this->mock(SubscriptionService::class)
            ->shouldReceive('canGenerate')->andReturn(true)
            ->shouldReceive('canSaveActivePlan')->andReturn(true)
            ->shouldReceive('getPlan')->andReturn(SubscriptionPlan::Free);

        $this->actingAsUser($user)->postJson('/api/v1/agent/generate-workout', $this->validRequestData);

        Queue::assertPushed(GenerateWorkoutPlanJob::class, function (GenerateWorkoutPlanJob $job) use ($user): bool {
            $state = $this->getJobWorkflowState($job);

            return $state['user_id'] === $user->id
                && $state['fitness_goals'] === ['muscle_gain']
                && $state['schedule']['training_days_per_week'] === 4
                && $state['equipment']['gym_access'] === true;
        });
    }

    public function test_returns_403_when_generation_limit_is_reached(): void
    {
        $user = User::factory()->create();

        $this->mock(SubscriptionService::class)
            ->shouldReceive('canGenerate')
            ->once()
            ->with(\Mockery::type(User::class))
            ->andReturn(false);

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent/generate-workout', $this->validRequestData);

        $response->assertForbidden();
    }

    public function test_returns_403_when_active_plans_limit_is_reached(): void
    {
        $user = User::factory()->create();

        $this->mock(SubscriptionService::class)
            ->shouldReceive('canGenerate')
            ->once()
            ->andReturn(true)
            ->shouldReceive('canSaveActivePlan')
            ->once()
            ->andReturn(false);

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent/generate-workout', $this->validRequestData);

        $response->assertForbidden();
    }

    public function test_returns_422_when_user_has_no_fitness_info(): void
    {
        $user = User::factory()->create();

        $this->mock(SubscriptionService::class)
            ->shouldReceive('canGenerate')->andReturn(true)
            ->shouldReceive('canSaveActivePlan')->andReturn(true);

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent/generate-workout', $this->validRequestData);

        $response->assertUnprocessable();
    }

    /**
     * Expose the private workflowState via reflection for assertion purposes.
     *
     * @return array<string, mixed>
     */
    private function getJobWorkflowState(GenerateWorkoutPlanJob $job): array
    {
        $reflection = new \ReflectionClass($job);
        $property = $reflection->getProperty('workflowState');

        return $property->getValue($job);
    }
}
