<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TokenAbility;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FeedbackControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(User $user): static
    {
        Sanctum::actingAs($user, [TokenAbility::ACCESS_API->value]);

        return $this;
    }

    // ==================== STORE ====================

    public function test_authenticated_user_can_submit_feedback_with_rating_and_message(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/feedbacks', [
            'rating' => 4,
            'message' => 'Great app, love the workout generation!',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'user_id', 'rating', 'message', 'created_at', 'updated_at'],
            ])
            ->assertJsonPath('data.rating', 4)
            ->assertJsonPath('data.message', 'Great app, love the workout generation!')
            ->assertJsonPath('data.user_id', $user->id);
    }

    public function test_authenticated_user_can_submit_feedback_with_rating_only(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/feedbacks', [
            'rating' => 5,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.rating', 5)
            ->assertJsonPath('data.message', null);
    }

    public function test_authenticated_user_can_submit_feedback_with_message_only(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/feedbacks', [
            'message' => 'I found a bug in the PDF export.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.rating', null)
            ->assertJsonPath('data.message', 'I found a bug in the PDF export.');
    }

    public function test_authenticated_user_can_submit_feedback_with_no_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/feedbacks', []);

        $response->assertCreated()
            ->assertJsonPath('data.rating', null)
            ->assertJsonPath('data.message', null);
    }

    public function test_store_persists_feedback_in_database(): void
    {
        $user = User::factory()->create();

        $this->actingAsUser($user)->postJson('/api/v1/feedbacks', [
            'rating' => 3,
            'message' => 'Could be improved.',
        ]);

        $this->assertDatabaseHas('feedbacks', [
            'user_id' => $user->id,
            'rating' => 3,
            'message' => 'Could be improved.',
        ]);
    }

    public function test_store_fails_with_rating_below_minimum(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/feedbacks', [
            'rating' => 0,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['rating']);
    }

    public function test_store_fails_with_rating_above_maximum(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/feedbacks', [
            'rating' => 6,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['rating']);
    }

    public function test_store_fails_with_message_exceeding_max_length(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/feedbacks', [
            'message' => str_repeat('a', 1001),
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['message']);
    }

    public function test_store_fails_with_non_integer_rating(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/feedbacks', [
            'rating' => 'five',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['rating']);
    }

    public function test_unauthenticated_user_cannot_submit_feedback(): void
    {
        $response = $this->postJson('/api/v1/feedbacks', [
            'rating' => 5,
            'message' => 'Trying without auth.',
        ]);

        $response->assertUnauthorized();
    }

    public function test_multiple_feedbacks_from_same_user_are_allowed(): void
    {
        $user = User::factory()->create();

        $this->actingAsUser($user)->postJson('/api/v1/feedbacks', ['rating' => 4]);
        $this->actingAsUser($user)->postJson('/api/v1/feedbacks', ['rating' => 5]);

        $this->assertSame(2, Feedback::query()->where('user_id', $user->id)->count());
    }

    // ==================== INDEX (admin) ====================

    public function test_admin_can_list_all_feedbacks(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Feedback::factory()->count(3)->create();

        $response = $this->actingAsUser($admin)->getJson('/api/v1/admin/feedbacks');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'user_id', 'rating', 'message', 'created_at', 'updated_at']],
            ]);
    }

    public function test_admin_feedbacks_response_includes_user_data(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();
        Feedback::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAsUser($admin)->getJson('/api/v1/admin/feedbacks');

        $response->assertOk()
            ->assertJsonPath('data.0.user.email', $user->email);
    }

    public function test_regular_user_cannot_list_all_feedbacks(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->getJson('/api/v1/admin/feedbacks');

        $response->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_list_feedbacks(): void
    {
        $response = $this->getJson('/api/v1/admin/feedbacks');

        $response->assertUnauthorized();
    }
}
