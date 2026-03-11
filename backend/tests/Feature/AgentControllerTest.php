<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TokenAbility;
use App\Models\FitnessInfo;
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

    private function actingAsUser(User $user): static
    {
        Sanctum::actingAs($user, [TokenAbility::ACCESS_API->value]);

        return $this;
    }

    // ==================== CALL — VALIDATION ====================

    public function test_unauthenticated_user_cannot_call_agent(): void
    {
        $response = $this->postJson('/api/v1/agent', ['message' => 'Give me a workout plan']);

        $response->assertUnauthorized();
    }

    public function test_call_requires_message(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['message']);
    }

    public function test_call_message_cannot_exceed_2000_characters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent', [
            'message' => str_repeat('a', 2001),
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['message']);
    }

    // ==================== RESUME — VALIDATION ====================

    public function test_unauthenticated_user_cannot_resume_agent(): void
    {
        $response = $this->postJson('/api/v1/agent/resume', [
            'resume_token' => 'some-token',
            'actions' => [['id' => 'use_saved_profile', 'decision' => 'approved']],
        ]);

        $response->assertUnauthorized();
    }

    public function test_resume_requires_resume_token(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent/resume', [
            'actions' => [['id' => 'use_saved_profile', 'decision' => 'approved']],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['resume_token']);
    }

    public function test_resume_requires_at_least_one_action(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent/resume', [
            'resume_token' => 'some-token',
            'actions' => [],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['actions']);
    }

    public function test_resume_action_requires_an_id(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent/resume', [
            'resume_token' => 'some-token',
            'actions' => [['decision' => 'approved']],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['actions.0.id']);
    }

    public function test_resume_action_decision_must_be_a_valid_value(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent/resume', [
            'resume_token' => 'some-token',
            'actions' => [['id' => 'use_saved_profile', 'decision' => 'invalid_decision']],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['actions.0.decision']);
    }

    public function test_resume_action_feedback_cannot_exceed_1000_characters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent/resume', [
            'resume_token' => 'some-token',
            'actions' => [[
                'id' => 'provide_height',
                'decision' => 'edit',
                'feedback' => str_repeat('a', 1001),
            ]],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['actions.0.feedback']);
    }

    // ==================== WORKFLOW — FIRST INTERRUPT ====================

    public function test_call_interrupts_asking_whether_to_use_saved_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/agent', [
            'message' => 'Give me a workout plan',
        ]);

        $response->assertStatus(202)
            ->assertJsonStructure(['status', 'resume_token', 'message', 'actions'])
            ->assertJsonPath('status', 'interrupted');

        $this->assertNotEmpty($response->json('resume_token'));

        $actionIds = array_column($response->json('actions'), 'id');
        $this->assertContains('use_saved_profile', $actionIds);
    }

    // ==================== WORKFLOW — RESUME: NO FITNESS INFO ====================

    public function test_user_with_no_fitness_info_rejecting_saved_profile_is_asked_for_height(): void
    {
        $user = User::factory()->create();

        // Step 1: Initial call — interrupt for saved profile question
        $callResponse = $this->actingAsUser($user)->postJson('/api/v1/agent', [
            'message' => 'Give me a workout plan',
        ]);
        $callResponse->assertStatus(202);
        $resumeToken = $callResponse->json('resume_token');

        // Step 2: Reject saved profile — no DB record exists, expect height question
        $resumeResponse = $this->actingAsUser($user)->postJson('/api/v1/agent/resume', [
            'resume_token' => $resumeToken,
            'actions' => [['id' => 'use_saved_profile', 'decision' => 'rejected']],
        ]);

        $resumeResponse->assertStatus(202)
            ->assertJsonPath('status', 'interrupted');

        $actionIds = array_column($resumeResponse->json('actions'), 'id');
        $this->assertContains('provide_height', $actionIds);
    }

    public function test_user_with_no_fitness_info_approving_saved_profile_is_still_asked_for_all_fields(): void
    {
        $user = User::factory()->create();

        // Step 1: Initial call
        $callResponse = $this->actingAsUser($user)->postJson('/api/v1/agent', [
            'message' => 'Give me a workout plan',
        ]);
        $resumeToken = $callResponse->json('resume_token');

        // Step 2: Approve saved profile — but no record in DB → agent falls back to asking all fields
        $resumeResponse = $this->actingAsUser($user)->postJson('/api/v1/agent/resume', [
            'resume_token' => $resumeToken,
            'actions' => [['id' => 'use_saved_profile', 'decision' => 'approved']],
        ]);

        $resumeResponse->assertStatus(202)
            ->assertJsonPath('status', 'interrupted');

        $actionIds = array_column($resumeResponse->json('actions'), 'id');
        $this->assertContains('provide_height', $actionIds);
    }

    // ==================== WORKFLOW — RESUME: PARTIAL FITNESS INFO ====================

    public function test_user_with_partial_fitness_info_is_asked_only_for_missing_fields(): void
    {
        $user = User::factory()->create();

        // Partial profile: height and weight exist; age, gender, experience_level are null
        FitnessInfo::factory()->partial()->create(['user_id' => $user->id]);

        // Step 1: Initial call
        $callResponse = $this->actingAsUser($user)->postJson('/api/v1/agent', [
            'message' => 'Give me a workout plan',
        ]);
        $callResponse->assertStatus(202);
        $resumeToken = $callResponse->json('resume_token');

        // Step 2: Approve saved profile — height and weight are loaded, age/gender/experience_level missing
        $resumeResponse = $this->actingAsUser($user)->postJson('/api/v1/agent/resume', [
            'resume_token' => $resumeToken,
            'actions' => [['id' => 'use_saved_profile', 'decision' => 'approved']],
        ]);

        // Agent should ask for the first missing field (age), NOT height or weight
        $resumeResponse->assertStatus(202)
            ->assertJsonPath('status', 'interrupted');

        $actionIds = array_column($resumeResponse->json('actions'), 'id');
        $this->assertContains('provide_age', $actionIds);
        $this->assertNotContains('provide_height', $actionIds);
        $this->assertNotContains('provide_weight', $actionIds);
    }

    public function test_user_with_partial_fitness_info_collects_all_missing_fields_sequentially(): void
    {
        $user = User::factory()->create();
        FitnessInfo::factory()->partial()->create(['user_id' => $user->id]);

        // Step 1: Initial call
        $callResponse = $this->actingAsUser($user)->postJson('/api/v1/agent', [
            'message' => 'Give me a workout plan',
        ]);
        $resumeToken = $callResponse->json('resume_token');

        // Step 2: Approve saved profile → asks for age (first missing field)
        $step2 = $this->actingAsUser($user)->postJson('/api/v1/agent/resume', [
            'resume_token' => $resumeToken,
            'actions' => [['id' => 'use_saved_profile', 'decision' => 'approved']],
        ]);
        $step2->assertStatus(202);
        $resumeToken = $step2->json('resume_token');

        // Step 3: Provide age → asks for gender
        $step3 = $this->actingAsUser($user)->postJson('/api/v1/agent/resume', [
            'resume_token' => $resumeToken,
            'actions' => [['id' => 'provide_age', 'decision' => 'edit', 'feedback' => '25']],
        ]);
        $step3->assertStatus(202);
        $actionIds = array_column($step3->json('actions'), 'id');
        $this->assertContains('provide_gender', $actionIds);
        $resumeToken = $step3->json('resume_token');

        // Step 4: Provide gender → asks for experience_level
        $step4 = $this->actingAsUser($user)->postJson('/api/v1/agent/resume', [
            'resume_token' => $resumeToken,
            'actions' => [['id' => 'provide_gender', 'decision' => 'edit', 'feedback' => 'male']],
        ]);
        $step4->assertStatus(202);
        $actionIds = array_column($step4->json('actions'), 'id');
        $this->assertContains('provide_experience_level', $actionIds);
    }

    // ==================== WORKFLOW — RESUME: COMPLETE FITNESS INFO ====================

    public function test_user_with_complete_fitness_info_workflow_completes_after_approving_saved_profile(): void
    {
        $mockMessage = Mockery::mock(AssistantMessage::class);
        $mockMessage->shouldReceive('getContent')->andReturn('Here is your personalized workout plan!');

        $mockAgent = Mockery::mock('overload:' . FitnessAgent::class);
        $mockAgent->shouldReceive('make')->andReturnSelf();
        $mockAgent->shouldReceive('chat')->andReturnSelf();
        $mockAgent->shouldReceive('getMessage')->andReturn($mockMessage);

        $user = User::factory()->create();
        FitnessInfo::factory()->create(['user_id' => $user->id]);

        // Step 1: Initial call
        $callResponse = $this->actingAsUser($user)->postJson('/api/v1/agent', [
            'message' => 'Give me a workout plan',
        ]);
        $callResponse->assertStatus(202);
        $resumeToken = $callResponse->json('resume_token');

        // Step 2: Approve saved profile — all fields present, workflow completes
        $resumeResponse = $this->actingAsUser($user)->postJson('/api/v1/agent/resume', [
            'resume_token' => $resumeToken,
            'actions' => [['id' => 'use_saved_profile', 'decision' => 'approved']],
        ]);

        $resumeResponse->assertOk()
            ->assertJsonPath('meta_data.response', 'Here is your personalized workout plan!');
    }

    // ==================== WORKFLOW — FIELD VALIDATION AND RETRY ====================

    public function test_providing_invalid_height_value_returns_retry_interrupt_with_attempt_info(): void
    {
        $user = User::factory()->create();

        // Step 1: Initial call
        $callResponse = $this->actingAsUser($user)->postJson('/api/v1/agent', [
            'message' => 'Give me a workout plan',
        ]);
        $resumeToken = $callResponse->json('resume_token');

        // Step 2: Reject saved profile → asked for height
        $step2 = $this->actingAsUser($user)->postJson('/api/v1/agent/resume', [
            'resume_token' => $resumeToken,
            'actions' => [['id' => 'use_saved_profile', 'decision' => 'rejected']],
        ]);
        $resumeToken = $step2->json('resume_token');

        // Step 3: Provide invalid height — expects retry interrupt mentioning attempt count
        $step3 = $this->actingAsUser($user)->postJson('/api/v1/agent/resume', [
            'resume_token' => $resumeToken,
            'actions' => [['id' => 'provide_height', 'decision' => 'edit', 'feedback' => 'not_a_number']],
        ]);

        $step3->assertStatus(202)
            ->assertJsonPath('status', 'interrupted');

        $this->assertStringContainsStringIgnoringCase('attempt', $step3->json('message'));
    }

    public function test_providing_out_of_range_height_returns_retry_interrupt(): void
    {
        $user = User::factory()->create();

        $callResponse = $this->actingAsUser($user)->postJson('/api/v1/agent', [
            'message' => 'Give me a workout plan',
        ]);
        $resumeToken = $callResponse->json('resume_token');

        $step2 = $this->actingAsUser($user)->postJson('/api/v1/agent/resume', [
            'resume_token' => $resumeToken,
            'actions' => [['id' => 'use_saved_profile', 'decision' => 'rejected']],
        ]);
        $resumeToken = $step2->json('resume_token');

        // Height must be between 50–300 cm; 500 is out of range
        $step3 = $this->actingAsUser($user)->postJson('/api/v1/agent/resume', [
            'resume_token' => $resumeToken,
            'actions' => [['id' => 'provide_height', 'decision' => 'edit', 'feedback' => '500']],
        ]);

        $step3->assertStatus(202)
            ->assertJsonPath('status', 'interrupted');
    }

    public function test_providing_invalid_gender_value_returns_retry_interrupt(): void
    {
        $user = User::factory()->create();
        FitnessInfo::factory()->partial()->create(['user_id' => $user->id]);

        $callResponse = $this->actingAsUser($user)->postJson('/api/v1/agent', [
            'message' => 'Give me a workout plan',
        ]);
        $resumeToken = $callResponse->json('resume_token');

        // Approve saved profile → age is the first missing field
        $step2 = $this->actingAsUser($user)->postJson('/api/v1/agent/resume', [
            'resume_token' => $resumeToken,
            'actions' => [['id' => 'use_saved_profile', 'decision' => 'approved']],
        ]);
        $resumeToken = $step2->json('resume_token');

        // Provide valid age → asked for gender
        $step3 = $this->actingAsUser($user)->postJson('/api/v1/agent/resume', [
            'resume_token' => $resumeToken,
            'actions' => [['id' => 'provide_age', 'decision' => 'edit', 'feedback' => '30']],
        ]);
        $resumeToken = $step3->json('resume_token');

        // Provide invalid gender value (only 'male' / 'female' are accepted)
        $step4 = $this->actingAsUser($user)->postJson('/api/v1/agent/resume', [
            'resume_token' => $resumeToken,
            'actions' => [['id' => 'provide_gender', 'decision' => 'edit', 'feedback' => 'unknown']],
        ]);

        $step4->assertStatus(202)
            ->assertJsonPath('status', 'interrupted');

        $this->assertStringContainsStringIgnoringCase('attempt', $step4->json('message'));
    }

    public function test_providing_invalid_experience_level_value_returns_retry_interrupt(): void
    {
        $user = User::factory()->create();
        FitnessInfo::factory()->partial()->create(['user_id' => $user->id]);

        $callResponse = $this->actingAsUser($user)->postJson('/api/v1/agent', [
            'message' => 'Give me a workout plan',
        ]);
        $resumeToken = $callResponse->json('resume_token');

        $step2 = $this->actingAsUser($user)->postJson('/api/v1/agent/resume', [
            'resume_token' => $resumeToken,
            'actions' => [['id' => 'use_saved_profile', 'decision' => 'approved']],
        ]);
        $resumeToken = $step2->json('resume_token');

        $step3 = $this->actingAsUser($user)->postJson('/api/v1/agent/resume', [
            'resume_token' => $resumeToken,
            'actions' => [['id' => 'provide_age', 'decision' => 'edit', 'feedback' => '28']],
        ]);
        $resumeToken = $step3->json('resume_token');

        $step4 = $this->actingAsUser($user)->postJson('/api/v1/agent/resume', [
            'resume_token' => $resumeToken,
            'actions' => [['id' => 'provide_gender', 'decision' => 'edit', 'feedback' => 'female']],
        ]);
        $resumeToken = $step4->json('resume_token');

        // Provide invalid experience level (only beginner/intermediate/advanced/professional accepted)
        $step5 = $this->actingAsUser($user)->postJson('/api/v1/agent/resume', [
            'resume_token' => $resumeToken,
            'actions' => [['id' => 'provide_experience_level', 'decision' => 'edit', 'feedback' => 'expert']],
        ]);

        $step5->assertStatus(202)
            ->assertJsonPath('status', 'interrupted');

        $this->assertStringContainsStringIgnoringCase('attempt', $step5->json('message'));
    }
}
