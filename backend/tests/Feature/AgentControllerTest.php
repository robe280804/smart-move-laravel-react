<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TokenAbility;
use App\Models\User;
use App\Models\WorkoutPlan;
use App\Services\WorkoutPlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class AgentControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<string, mixed> */
    private array $validRequestData = [
        'fitness_goals'          => ['muscle_gain'],
        'training_days_per_week' => 4,
        'available_days'         => ['monday', 'tuesday', 'thursday', 'friday'],
        'session_duration'       => 60,
        'equipment'              => ['barbell', 'dumbbells'],
        'gym_access'             => true,
        'workout_type'           => ['strength'],
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

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_returns_201_with_workout_plan_resource_on_success(): void
    {
        $workflowMock = \Mockery::mock('overload:App\Neuron\FitnessAgentWorkflow');
        $workflowMock->shouldReceive('create')->andReturnSelf();
        $workflowMock->shouldReceive('init')->andReturnSelf();
        $workflowMock->shouldReceive('run')->andReturnSelf();

        $user = User::factory()->create();
        $plan = WorkoutPlan::factory()->make(['id' => 1, 'user_id' => $user->id]);
        $plan->setRelation('planDays', collect());

        $this->mock(WorkoutPlanService::class)
            ->shouldReceive('createFromAgentResponse')
            ->once()
            ->andReturn($plan);

        Sanctum::actingAs($user, [TokenAbility::ACCESS_API->value]);

        $response = $this->postJson('/api/v1/agent/generate-workout', $this->validRequestData);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'training_days_per_week',
                    'goal',
                    'experience_level',
                    'workout_type',
                    'plan_days',
                ],
            ]);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_accepts_optional_nullable_fields(): void
    {
        $workflowMock = \Mockery::mock('overload:App\Neuron\FitnessAgentWorkflow');
        $workflowMock->shouldReceive('create')->andReturnSelf();
        $workflowMock->shouldReceive('init')->andReturnSelf();
        $workflowMock->shouldReceive('run')->andReturnSelf();

        $user = User::factory()->create();
        $plan = WorkoutPlan::factory()->make(['id' => 1, 'user_id' => $user->id]);
        $plan->setRelation('planDays', collect());

        $this->mock(WorkoutPlanService::class)
            ->shouldReceive('createFromAgentResponse')
            ->once()
            ->andReturn($plan);

        Sanctum::actingAs($user, [TokenAbility::ACCESS_API->value]);

        $data = array_merge($this->validRequestData, [
            'injuries'            => 'left knee pain',
            'sports'              => 'cycling',
            'preferred_exercises' => 'squats, deadlifts',
            'additional_notes'    => 'prefer morning sessions',
        ]);

        $response = $this->postJson('/api/v1/agent/generate-workout', $data);

        $response->assertCreated();
    }
}
