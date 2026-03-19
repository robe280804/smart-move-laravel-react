<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TokenAbility;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(User $user): static
    {
        Sanctum::actingAs($user, [TokenAbility::ACCESS_API->value]);

        return $this;
    }

    // ==================== DESTROY ====================

    public function test_user_can_delete_their_own_account(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->deleteJson("/api/v1/users/{$user->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_user_cannot_delete_another_users_account(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $response = $this->actingAsUser($user)->deleteJson("/api/v1/users/{$other->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $other->id]);
    }

    public function test_unauthenticated_request_cannot_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/v1/users/{$user->id}");

        $response->assertUnauthorized();
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_deleting_account_with_active_subscription_still_removes_user(): void
    {
        // Cashier uses the Stripe PHP SDK (cURL), not Laravel's HTTP client,
        // so cancelNow() will throw in test environments. The service catches this
        // and proceeds with deletion — verifying that behaviour here.
        $user = User::factory()->create(['stripe_id' => 'cus_test_123']);
        $user->subscriptions()->create([
            'type' => 'default',
            'stripe_id' => 'sub_test_active',
            'stripe_status' => 'active',
            'stripe_price' => 'price_test_pro',
            'quantity' => 1,
        ]);

        $response = $this->actingAsUser($user)->deleteJson("/api/v1/users/{$user->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_deleting_account_with_cancelled_subscription_removes_user(): void
    {
        $user = User::factory()->create();
        $user->subscriptions()->create([
            'type' => 'default',
            'stripe_id' => 'sub_already_cancelled',
            'stripe_status' => 'canceled',
            'stripe_price' => 'price_test_pro',
            'quantity' => 1,
        ]);

        $response = $this->actingAsUser($user)->deleteJson("/api/v1/users/{$user->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_deleting_account_with_trialing_subscription_still_removes_user(): void
    {
        $user = User::factory()->create(['stripe_id' => 'cus_test_456']);
        $user->subscriptions()->create([
            'type' => 'default',
            'stripe_id' => 'sub_test_trial',
            'stripe_status' => 'trialing',
            'stripe_price' => 'price_test_advanced',
            'quantity' => 1,
            'trial_ends_at' => now()->addDays(7),
        ]);

        $response = $this->actingAsUser($user)->deleteJson("/api/v1/users/{$user->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
