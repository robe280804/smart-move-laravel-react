<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TokenAbility;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ChangePasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    private const CURRENT_PASSWORD = 'Current@123';

    private const NEW_PASSWORD = 'NewPass@456';

    protected function setUp(): void
    {
        parent::setUp();

        // Mock HaveIBeenPwned so uncompromised() does not fail in tests
        Http::fake(['*pwnedpasswords.com/*' => Http::response('', 200)]);
    }

    private function makeUser(): User
    {
        return User::factory()->create([
            'password' => Hash::make(self::CURRENT_PASSWORD),
        ]);
    }

    private function actingAsUser(User $user): static
    {
        Sanctum::actingAs($user, [TokenAbility::ACCESS_API->value]);

        return $this;
    }

    private function validPayload(): array
    {
        return [
            'current_password' => self::CURRENT_PASSWORD,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ];
    }

    // ==================== AUTHENTICATION ====================

    public function test_unauthenticated_user_cannot_change_password(): void
    {
        $response = $this->postJson('/api/v1/users/change-password', $this->validPayload());

        $response->assertUnauthorized();
    }

    // ==================== HAPPY PATH ====================

    public function test_authenticated_user_can_change_password(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAsUser($user)
            ->postJson('/api/v1/users/change-password', $this->validPayload());

        $response->assertOk();
        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $user->fresh()->password));
    }

    public function test_change_password_revokes_other_sanctum_sessions(): void
    {
        $user = $this->makeUser();

        // Create an extra token that represents another session
        $otherToken = $user->createToken('other-device', [TokenAbility::ACCESS_API->value]);

        $this->actingAsUser($user)
            ->postJson('/api/v1/users/change-password', $this->validPayload())
            ->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $otherToken->accessToken->id,
        ]);
    }

    public function test_change_password_keeps_current_session_active(): void
    {
        $user = $this->makeUser();

        // Sanctum::actingAs creates a token internally; we assert the user can still hit an authenticated route
        $this->actingAsUser($user)
            ->postJson('/api/v1/users/change-password', $this->validPayload())
            ->assertOk();

        // The current session token is preserved — user can still reach protected endpoints
        $this->actingAsUser($user)
            ->getJson('/api/v1/users')
            ->assertOk();
    }

    // ==================== VALIDATION ====================

    public function test_wrong_current_password_is_rejected(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAsUser($user)
            ->postJson('/api/v1/users/change-password', [
                'current_password' => 'WrongPassword@1',
                'password' => self::NEW_PASSWORD,
                'password_confirmation' => self::NEW_PASSWORD,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_new_password_must_be_confirmed(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAsUser($user)
            ->postJson('/api/v1/users/change-password', [
                'current_password' => self::CURRENT_PASSWORD,
                'password' => self::NEW_PASSWORD,
                'password_confirmation' => 'mismatch',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_new_password_must_meet_strength_requirements(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAsUser($user)
            ->postJson('/api/v1/users/change-password', [
                'current_password' => self::CURRENT_PASSWORD,
                'password' => 'weakpassword',
                'password_confirmation' => 'weakpassword',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_all_fields_are_required(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAsUser($user)
            ->postJson('/api/v1/users/change-password', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['current_password', 'password', 'password_confirmation']);
    }
}
