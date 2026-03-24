<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TokenAbility;
use App\Models\FitnessInfo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ExportUserDataTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(User $user): static
    {
        Sanctum::actingAs($user, [TokenAbility::ACCESS_API->value]);

        return $this;
    }

    public function test_user_can_export_their_own_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)
            ->getJson("/api/v1/users/{$user->id}/export");

        $response->assertOk();
        $response->assertJsonStructure([
            'exported_at',
            'profile' => ['id', 'name', 'surname', 'email', 'role', 'email_verified_at', 'account_created_at'],
            'fitness_info',
            'workout_plans',
            'feedback',
        ]);
        $response->assertJsonPath('profile.id', $user->id);
        $response->assertJsonPath('profile.email', $user->email);
        $response->assertHeader('Content-Disposition', 'attachment; filename="user-data-export.json"');
    }

    public function test_export_includes_fitness_info_when_present(): void
    {
        $user = User::factory()->create();
        FitnessInfo::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAsUser($user)
            ->getJson("/api/v1/users/{$user->id}/export");

        $response->assertOk();
        $response->assertJsonStructure([
            'fitness_info' => ['height_cm', 'weight_kg', 'age', 'gender', 'experience_level', 'recorded_at'],
        ]);
    }

    public function test_export_returns_null_fitness_info_when_absent(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)
            ->getJson("/api/v1/users/{$user->id}/export");

        $response->assertOk();
        $response->assertJsonPath('fitness_info', null);
    }

    public function test_user_cannot_export_another_users_data(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $response = $this->actingAsUser($user)
            ->getJson("/api/v1/users/{$other->id}/export");

        $response->assertForbidden();
    }

    public function test_unauthenticated_request_cannot_export_data(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/v1/users/{$user->id}/export");

        $response->assertUnauthorized();
    }
}
