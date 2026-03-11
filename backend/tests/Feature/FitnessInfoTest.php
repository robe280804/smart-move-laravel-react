<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ExperienceLevel;
use App\Enums\Gender;
use App\Enums\TokenAbility;
use App\Models\FitnessInfo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FitnessInfoTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<string, mixed> */
    private array $validPayload = [
        'height' => 175.5,
        'weight' => 70.0,
        'age' => 25,
        'gender' => 'male',
        'experience_level' => 'beginner',
    ];

    private function actingAsUser(User $user): static
    {
        Sanctum::actingAs($user, [TokenAbility::ACCESS_API->value]);

        return $this;
    }

    // ==================== INDEX ====================

    public function test_authenticated_user_can_retrieve_own_fitness_info(): void
    {
        $user = User::factory()->create();
        $fitnessInfo = FitnessInfo::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAsUser($user)->getJson('/api/v1/fitness-info');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'user_id', 'height', 'weight', 'age', 'gender', 'experience_level', 'created_at', 'updated_at'],
            ])
            ->assertJsonPath('data.id', $fitnessInfo->id)
            ->assertJsonPath('data.user_id', $user->id);
    }

    public function test_index_returns_not_found_when_user_has_no_fitness_info(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->getJson('/api/v1/fitness-info');

        $response->assertNotFound();
    }

    public function test_unauthenticated_user_cannot_retrieve_fitness_info(): void
    {
        $response = $this->getJson('/api/v1/fitness-info');

        $response->assertUnauthorized();
    }

    // ==================== STORE ====================

    public function test_authenticated_user_can_create_fitness_info(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/fitness-info', $this->validPayload);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'user_id', 'height', 'weight', 'age', 'gender', 'experience_level', 'created_at', 'updated_at'],
            ])
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.age', 25);
    }

    public function test_store_creates_fitness_info_in_database(): void
    {
        $user = User::factory()->create();

        $this->actingAsUser($user)->postJson('/api/v1/fitness-info', $this->validPayload);

        $this->assertDatabaseHas('fitness_infos', [
            'user_id' => $user->id,
            'age' => 25,
            'gender' => Gender::Male->value,
            'experience_level' => ExperienceLevel::Beginner->value,
        ]);
    }

    public function test_user_cannot_create_fitness_info_if_one_already_exists(): void
    {
        $user = User::factory()->create();
        FitnessInfo::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAsUser($user)->postJson('/api/v1/fitness-info', $this->validPayload);

        $response->assertForbidden();
    }

    public function test_store_fails_without_height(): void
    {
        $user = User::factory()->create();
        $payload = array_diff_key($this->validPayload, ['height' => '']);

        $response = $this->actingAsUser($user)->postJson('/api/v1/fitness-info', $payload);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['height']);
    }

    public function test_store_fails_without_weight(): void
    {
        $user = User::factory()->create();
        $payload = array_diff_key($this->validPayload, ['weight' => '']);

        $response = $this->actingAsUser($user)->postJson('/api/v1/fitness-info', $payload);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['weight']);
    }

    public function test_store_fails_without_age(): void
    {
        $user = User::factory()->create();
        $payload = array_diff_key($this->validPayload, ['age' => '']);

        $response = $this->actingAsUser($user)->postJson('/api/v1/fitness-info', $payload);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['age']);
    }

    public function test_store_fails_without_gender(): void
    {
        $user = User::factory()->create();
        $payload = array_diff_key($this->validPayload, ['gender' => '']);

        $response = $this->actingAsUser($user)->postJson('/api/v1/fitness-info', $payload);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['gender']);
    }

    public function test_store_fails_without_experience_level(): void
    {
        $user = User::factory()->create();
        $payload = array_diff_key($this->validPayload, ['experience_level' => '']);

        $response = $this->actingAsUser($user)->postJson('/api/v1/fitness-info', $payload);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['experience_level']);
    }

    public function test_store_fails_with_invalid_gender_value(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/fitness-info', array_merge($this->validPayload, [
            'gender' => 'invalid',
        ]));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['gender']);
    }

    public function test_store_fails_with_invalid_experience_level_value(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/fitness-info', array_merge($this->validPayload, [
            'experience_level' => 'invalid',
        ]));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['experience_level']);
    }

    public function test_store_fails_with_height_below_minimum(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/fitness-info', array_merge($this->validPayload, [
            'height' => 10,
        ]));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['height']);
    }

    public function test_store_fails_with_height_above_maximum(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/fitness-info', array_merge($this->validPayload, [
            'height' => 400,
        ]));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['height']);
    }

    public function test_store_fails_with_weight_below_minimum(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/fitness-info', array_merge($this->validPayload, [
            'weight' => 5,
        ]));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['weight']);
    }

    public function test_store_fails_with_weight_above_maximum(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/fitness-info', array_merge($this->validPayload, [
            'weight' => 600,
        ]));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['weight']);
    }

    public function test_store_fails_with_age_below_minimum(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/fitness-info', array_merge($this->validPayload, [
            'age' => 5,
        ]));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['age']);
    }

    public function test_store_fails_with_age_above_maximum(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/fitness-info', array_merge($this->validPayload, [
            'age' => 200,
        ]));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['age']);
    }

    public function test_unauthenticated_user_cannot_create_fitness_info(): void
    {
        $response = $this->postJson('/api/v1/fitness-info', $this->validPayload);

        $response->assertUnauthorized();
    }

    // ==================== SHOW ====================

    public function test_owner_can_view_own_fitness_info(): void
    {
        $user = User::factory()->create();
        $fitnessInfo = FitnessInfo::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAsUser($user)->getJson("/api/v1/fitness-info/{$fitnessInfo->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'user_id', 'height', 'weight', 'age', 'gender', 'experience_level', 'created_at', 'updated_at'],
            ])
            ->assertJsonPath('data.id', $fitnessInfo->id);
    }

    public function test_user_cannot_view_another_users_fitness_info(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $fitnessInfo = FitnessInfo::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAsUser($otherUser)->getJson("/api/v1/fitness-info/{$fitnessInfo->id}");

        $response->assertForbidden();
    }

    public function test_show_returns_not_found_for_nonexistent_fitness_info(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->getJson('/api/v1/fitness-info/99999');

        $response->assertNotFound();
    }

    public function test_unauthenticated_user_cannot_view_fitness_info(): void
    {
        $fitnessInfo = FitnessInfo::factory()->create();

        $response = $this->getJson("/api/v1/fitness-info/{$fitnessInfo->id}");

        $response->assertUnauthorized();
    }

    // ==================== UPDATE ====================

    public function test_owner_can_fully_update_own_fitness_info(): void
    {
        $user = User::factory()->create();
        $fitnessInfo = FitnessInfo::factory()->create(['user_id' => $user->id]);

        $updatedPayload = [
            'height' => 180.0,
            'weight' => 80.0,
            'age' => 30,
            'gender' => 'female',
            'experience_level' => 'advanced',
        ];

        $response = $this->actingAsUser($user)->putJson("/api/v1/fitness-info/{$fitnessInfo->id}", $updatedPayload);

        $response->assertOk()
            ->assertJsonPath('data.age', 30)
            ->assertJsonPath('data.gender', Gender::Female->value)
            ->assertJsonPath('data.experience_level', ExperienceLevel::Advanced->value);
    }

    public function test_owner_can_partially_update_own_fitness_info(): void
    {
        $user = User::factory()->create();
        $fitnessInfo = FitnessInfo::factory()->create(['user_id' => $user->id, 'age' => 25]);

        $response = $this->actingAsUser($user)->patchJson("/api/v1/fitness-info/{$fitnessInfo->id}", [
            'age' => 30,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.age', 30);
    }

    public function test_update_persists_changes_in_database(): void
    {
        $user = User::factory()->create();
        $fitnessInfo = FitnessInfo::factory()->create(['user_id' => $user->id]);

        $this->actingAsUser($user)->putJson("/api/v1/fitness-info/{$fitnessInfo->id}", [
            'age' => 40,
            'gender' => 'female',
        ]);

        $this->assertDatabaseHas('fitness_infos', [
            'id' => $fitnessInfo->id,
            'age' => 40,
            'gender' => Gender::Female->value,
        ]);
    }

    public function test_user_cannot_update_another_users_fitness_info(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $fitnessInfo = FitnessInfo::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAsUser($otherUser)->putJson("/api/v1/fitness-info/{$fitnessInfo->id}", [
            'age' => 30,
        ]);

        $response->assertForbidden();
    }

    public function test_update_fails_with_invalid_gender_value(): void
    {
        $user = User::factory()->create();
        $fitnessInfo = FitnessInfo::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAsUser($user)->patchJson("/api/v1/fitness-info/{$fitnessInfo->id}", [
            'gender' => 'invalid',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['gender']);
    }

    public function test_update_fails_with_height_out_of_range(): void
    {
        $user = User::factory()->create();
        $fitnessInfo = FitnessInfo::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAsUser($user)->patchJson("/api/v1/fitness-info/{$fitnessInfo->id}", [
            'height' => 10,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['height']);
    }

    public function test_unauthenticated_user_cannot_update_fitness_info(): void
    {
        $fitnessInfo = FitnessInfo::factory()->create();

        $response = $this->putJson("/api/v1/fitness-info/{$fitnessInfo->id}", ['age' => 30]);

        $response->assertUnauthorized();
    }

    // ==================== DESTROY ====================

    public function test_owner_can_delete_own_fitness_info(): void
    {
        $user = User::factory()->create();
        $fitnessInfo = FitnessInfo::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAsUser($user)->deleteJson("/api/v1/fitness-info/{$fitnessInfo->id}");

        $response->assertNoContent();
    }

    public function test_delete_removes_fitness_info_from_database(): void
    {
        $user = User::factory()->create();
        $fitnessInfo = FitnessInfo::factory()->create(['user_id' => $user->id]);

        $this->actingAsUser($user)->deleteJson("/api/v1/fitness-info/{$fitnessInfo->id}");

        $this->assertDatabaseMissing('fitness_infos', ['id' => $fitnessInfo->id]);
    }

    public function test_user_cannot_delete_another_users_fitness_info(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $fitnessInfo = FitnessInfo::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAsUser($otherUser)->deleteJson("/api/v1/fitness-info/{$fitnessInfo->id}");

        $response->assertForbidden();
    }

    public function test_delete_returns_not_found_for_nonexistent_fitness_info(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->deleteJson('/api/v1/fitness-info/99999');

        $response->assertNotFound();
    }

    public function test_unauthenticated_user_cannot_delete_fitness_info(): void
    {
        $fitnessInfo = FitnessInfo::factory()->create();

        $response = $this->deleteJson("/api/v1/fitness-info/{$fitnessInfo->id}");

        $response->assertUnauthorized();
    }
}
