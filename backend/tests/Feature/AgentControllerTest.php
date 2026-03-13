<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TokenAbility;
use App\Models\User;
use App\Neuron\FitnessAgent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Mockery;
use NeuronAI\Chat\Messages\AssistantMessage;
use Tests\TestCase;

class AgentControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<string, mixed> */
    private array $validFitnessData = [
        'age'                    => 28,
        'weight'                 => 75,
        'height'                 => 180,
        'gender'                 => 'male',
        'experience_level'       => 'intermediate',
        'fitness_goal'           => 'muscle_gain',
        'training_days_per_week' => 4,
        'available_days'         => ['monday', 'tuesday', 'thursday', 'friday'],
        'session_duration'       => 60,
    ];

    private function actingAsUser(User $user): static
    {
        Sanctum::actingAs($user, [TokenAbility::ACCESS_API->value]);

        return $this;
    }

    private function mockFitnessAgent(string $responseText = 'Here is your personalized workout plan!'): void
    {
        $mockMessage = Mockery::mock(AssistantMessage::class);
        $mockMessage->shouldReceive('getContent')->andReturn($responseText);

        $mockAgent = Mockery::mock('overload:' . FitnessAgent::class);
        $mockAgent->shouldReceive('make')->andReturnSelf();
        $mockAgent->shouldReceive('chat')->andReturnSelf();
        $mockAgent->shouldReceive('getMessage')->andReturn($mockMessage);
    }

    // ==================== AUTHENTICATION ====================

    public function test_unauthenticated_user_cannot_call_agent(): void
    {
        $response = $this->postJson('/api/v1/agent', [
            'fitness_data' => $this->validFitnessData,
        ]);

        $response->assertUnauthorized();
    }

    // ==================== VALIDATION — fitness_data required ====================

    public function test_call_requires_fitness_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['fitness_data']);
    }

    public function test_call_requires_age(): void
    {
        $user = User::factory()->create();
        $data = $this->validFitnessData;
        unset($data['age']);

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent', ['fitness_data' => $data]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['fitness_data.age']);
    }

    public function test_call_requires_valid_gender(): void
    {
        $user = User::factory()->create();
        $data = array_merge($this->validFitnessData, ['gender' => 'unknown']);

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent', ['fitness_data' => $data]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['fitness_data.gender']);
    }

    public function test_call_requires_valid_experience_level(): void
    {
        $user = User::factory()->create();
        $data = array_merge($this->validFitnessData, ['experience_level' => 'expert']);

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent', ['fitness_data' => $data]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['fitness_data.experience_level']);
    }

    public function test_call_requires_valid_fitness_goal(): void
    {
        $user = User::factory()->create();
        $data = array_merge($this->validFitnessData, ['fitness_goal' => 'invalid_goal']);

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent', ['fitness_data' => $data]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['fitness_data.fitness_goal']);
    }

    public function test_call_requires_valid_available_days(): void
    {
        $user = User::factory()->create();
        $data = array_merge($this->validFitnessData, ['available_days' => ['funday']]);

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent', ['fitness_data' => $data]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['fitness_data.available_days.0']);
    }

    public function test_call_rejects_age_out_of_range(): void
    {
        $user = User::factory()->create();
        $data = array_merge($this->validFitnessData, ['age' => 5]);

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent', ['fitness_data' => $data]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['fitness_data.age']);
    }

    public function test_call_rejects_message_exceeding_2000_characters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent', [
            'message'      => str_repeat('a', 2001),
            'fitness_data' => $this->validFitnessData,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['message']);
    }

    // ==================== HAPPY PATH ====================

    public function test_call_with_valid_data_returns_agent_response(): void
    {
        $this->mockFitnessAgent('Here is your personalized workout plan!');

        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent', [
            'fitness_data' => $this->validFitnessData,
        ]);

        $response->assertOk()
            ->assertJsonPath('meta_data.response', 'Here is your personalized workout plan!');
    }

    public function test_call_with_optional_message_passes_it_to_agent(): void
    {
        $this->mockFitnessAgent('Plan generated with your request in mind.');

        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent', [
            'message'      => 'Focus on upper body please',
            'fitness_data' => $this->validFitnessData,
        ]);

        $response->assertOk()
            ->assertJsonPath('meta_data.response', 'Plan generated with your request in mind.');
    }

    public function test_call_with_optional_nullable_fields_succeeds(): void
    {
        $this->mockFitnessAgent('Here is your plan!');

        $user = User::factory()->create();

        $data = array_merge($this->validFitnessData, [
            'rest_days'              => 2,
            'injuries'               => 'left knee pain',
            'equipment'              => 'dumbbells, barbell',
            'preferred_workout_type' => 'strength',
        ]);

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent', [
            'fitness_data' => $data,
        ]);

        $response->assertOk();
    }

    // ==================== RESUME ENDPOINT REMOVED ====================

    public function test_resume_endpoint_no_longer_exists(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent/resume', [
            'resume_token' => 'some-token',
            'actions'      => [['id' => 'use_saved_profile', 'decision' => 'approved']],
        ]);

        $response->assertNotFound();
    }
}
