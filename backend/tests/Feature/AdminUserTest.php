<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TokenAbility;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(User $user): static
    {
        Sanctum::actingAs($user, [TokenAbility::ACCESS_API->value]);

        return $this;
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        return $admin;
    }

    // ==================== UPDATE ====================

    public function test_admin_can_update_user_name_and_surname(): void
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();

        $response = $this->actingAsUser($admin)->putJson("/api/v1/admin/users/{$user->id}", [
            'name' => 'Updated',
            'surname' => 'User',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated')
            ->assertJsonPath('data.surname', 'User');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated',
            'surname' => 'User',
        ]);
    }

    public function test_admin_can_update_user_email(): void
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();

        $response = $this->actingAsUser($admin)->putJson("/api/v1/admin/users/{$user->id}", [
            'email' => 'newemail@example.com',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.email', 'newemail@example.com');
    }

    public function test_admin_can_update_user_role(): void
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();

        $response = $this->actingAsUser($admin)->putJson("/api/v1/admin/users/{$user->id}", [
            'role' => 'admin',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.role', 'admin');

        $this->assertTrue($user->fresh()->hasRole('admin'));
    }

    public function test_admin_cannot_update_own_account(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAsUser($admin)->putJson("/api/v1/admin/users/{$admin->id}", [
            'name' => 'Hacked',
        ]);

        $response->assertForbidden();
    }

    public function test_non_admin_cannot_update_users(): void
    {
        $user = User::factory()->create();
        $target = User::factory()->create();

        $response = $this->actingAsUser($user)->putJson("/api/v1/admin/users/{$target->id}", [
            'name' => 'Hacked',
        ]);

        $response->assertForbidden();
    }

    public function test_admin_update_rejects_invalid_email(): void
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();

        $response = $this->actingAsUser($admin)->putJson("/api/v1/admin/users/{$user->id}", [
            'email' => 'not-an-email',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_admin_update_rejects_duplicate_email(): void
    {
        $admin = $this->createAdmin();
        $existing = User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create();

        $response = $this->actingAsUser($admin)->putJson("/api/v1/admin/users/{$user->id}", [
            'email' => 'taken@example.com',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_admin_update_rejects_invalid_role(): void
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();

        $response = $this->actingAsUser($admin)->putJson("/api/v1/admin/users/{$user->id}", [
            'role' => 'superadmin',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['role']);
    }

    // ==================== DELETE ====================

    public function test_admin_can_delete_user(): void
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();

        $response = $this->actingAsUser($admin)->deleteJson("/api/v1/admin/users/{$user->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAsUser($admin)->deleteJson("/api/v1/admin/users/{$admin->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_non_admin_cannot_delete_users(): void
    {
        $user = User::factory()->create();
        $target = User::factory()->create();

        $response = $this->actingAsUser($user)->deleteJson("/api/v1/admin/users/{$target->id}");

        $response->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_access_admin_endpoints(): void
    {
        $user = User::factory()->create();

        $this->putJson("/api/v1/admin/users/{$user->id}", ['name' => 'Test'])
            ->assertUnauthorized();

        $this->deleteJson("/api/v1/admin/users/{$user->id}")
            ->assertUnauthorized();
    }
}
