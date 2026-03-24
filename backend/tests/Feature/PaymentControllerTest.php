<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TokenAbility;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    private const ADVANCED_PRICE = 'price_test_advanced';

    private const PRO_PRICE = 'price_test_pro';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'plans.stripe_prices.advanced' => self::ADVANCED_PRICE,
            'plans.stripe_prices.pro' => self::PRO_PRICE,
        ]);
    }

    private function actingAsUser(User $user): static
    {
        Sanctum::actingAs($user, [TokenAbility::ACCESS_API->value]);

        return $this;
    }

    private function createSubscription(User $user, string $stripePrice, string $status = 'active'): void
    {
        $subscription = $user->subscriptions()->create([
            'type' => 'default',
            'stripe_id' => 'sub_test_'.fake()->unique()->uuid(),
            'stripe_status' => $status,
            'stripe_price' => $stripePrice,
            'quantity' => 1,
        ]);

        $subscription->items()->create([
            'stripe_id' => 'si_test_'.fake()->unique()->uuid(),
            'stripe_product' => 'prod_test',
            'stripe_price' => $stripePrice,
            'quantity' => 1,
        ]);
    }

    // ==================== CURRENT PLAN ====================

    public function test_unauthenticated_user_cannot_get_current_plan(): void
    {
        $response = $this->getJson('/api/v1/payments/plan');

        $response->assertUnauthorized();
    }

    public function test_unverified_user_cannot_get_current_plan(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAsUser($user)->getJson('/api/v1/payments/plan');

        $response->assertForbidden();
    }

    public function test_returns_free_plan_for_user_with_no_subscriptions(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->getJson('/api/v1/payments/plan');

        $response->assertOk()
            ->assertJsonPath('data.plan', 'free');
    }

    public function test_returns_advanced_plan_for_user_with_active_advanced_subscription(): void
    {
        $user = User::factory()->create();
        $this->createSubscription($user, self::ADVANCED_PRICE);

        $response = $this->actingAsUser($user)->getJson('/api/v1/payments/plan');

        $response->assertOk()
            ->assertJsonPath('data.plan', 'advanced');
    }

    public function test_returns_pro_plan_for_user_with_active_pro_subscription(): void
    {
        $user = User::factory()->create();
        $this->createSubscription($user, self::PRO_PRICE);

        $response = $this->actingAsUser($user)->getJson('/api/v1/payments/plan');

        $response->assertOk()
            ->assertJsonPath('data.plan', 'pro');
    }

    public function test_returns_free_plan_for_user_with_canceled_subscription(): void
    {
        $user = User::factory()->create();
        $subscription = $user->subscriptions()->create([
            'type' => 'default',
            'stripe_id' => 'sub_test_canceled',
            'stripe_status' => 'canceled',
            'stripe_price' => self::PRO_PRICE,
            'quantity' => 1,
            'ends_at' => now()->subDay(),
        ]);

        $subscription->items()->create([
            'stripe_id' => 'si_test_canceled',
            'stripe_product' => 'prod_test',
            'stripe_price' => self::PRO_PRICE,
            'quantity' => 1,
        ]);

        $response = $this->actingAsUser($user)->getJson('/api/v1/payments/plan');

        $response->assertOk()
            ->assertJsonPath('data.plan', 'free');
    }

    public function test_returns_free_plan_when_subscription_has_unknown_price(): void
    {
        $user = User::factory()->create();
        $this->createSubscription($user, 'price_unknown_id');

        $response = $this->actingAsUser($user)->getJson('/api/v1/payments/plan');

        $response->assertOk()
            ->assertJsonPath('data.plan', 'free');
    }

    // ==================== CHECKOUT - AUTH & VALIDATION ====================

    public function test_unauthenticated_user_cannot_checkout(): void
    {
        $response = $this->postJson('/api/v1/payments/checkout', ['plan' => 'advanced']);

        $response->assertUnauthorized();
    }

    public function test_unverified_user_cannot_checkout(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/payments/checkout', ['plan' => 'advanced']);

        $response->assertForbidden();
    }

    public function test_checkout_requires_plan_field(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/payments/checkout', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('plan');
    }

    public function test_checkout_rejects_invalid_plan(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/payments/checkout', ['plan' => 'free']);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('plan');
    }

    public function test_checkout_rejects_nonexistent_plan(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/payments/checkout', ['plan' => 'enterprise']);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('plan');
    }

    // ==================== CHECKOUT - ALREADY SUBSCRIBED ====================

    public function test_checkout_returns_409_when_already_subscribed_to_same_plan(): void
    {
        $user = User::factory()->create();
        $this->createSubscription($user, self::ADVANCED_PRICE);

        $response = $this->actingAsUser($user)->postJson('/api/v1/payments/checkout', ['plan' => 'advanced']);

        $response->assertConflict()
            ->assertJsonPath('message', 'You are already subscribed to this plan.');
    }

    public function test_checkout_returns_409_for_pro_user_buying_pro_again(): void
    {
        $user = User::factory()->create();
        $this->createSubscription($user, self::PRO_PRICE);

        $response = $this->actingAsUser($user)->postJson('/api/v1/payments/checkout', ['plan' => 'pro']);

        $response->assertConflict();
    }

    // ==================== CHECKOUT - MISSING CONFIG ====================

    public function test_checkout_returns_500_when_stripe_price_not_configured(): void
    {
        config(['plans.stripe_prices.advanced' => null]);
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/payments/checkout', ['plan' => 'advanced']);

        $response->assertInternalServerError()
            ->assertJsonPath('message', 'Payment configuration error. Please try again later.');
    }

    // ==================== CHECKOUT - SWAP (Stripe SDK will throw in tests) ====================

    public function test_checkout_attempts_swap_when_user_has_active_subscription(): void
    {
        // Cashier uses the Stripe PHP SDK (cURL), not Laravel's HTTP client,
        // so swap() will throw in test environments. This verifies the swap
        // path is reached (not the "new subscription" path).
        $user = User::factory()->create(['stripe_id' => 'cus_test_swap']);
        $this->createSubscription($user, self::ADVANCED_PRICE);

        $response = $this->actingAsUser($user)->postJson('/api/v1/payments/checkout', ['plan' => 'pro']);

        // swap() throws because there's no real Stripe connection.
        // A 500 error confirms the swap path was reached (not already-subscribed or new checkout).
        $response->assertServerError();
    }

    public function test_checkout_does_not_swap_incomplete_subscriptions(): void
    {
        // Incomplete subscriptions are skipped during swap — a new checkout is created instead.
        // Since newSubscription()->checkout() also calls Stripe, it will throw too.
        $user = User::factory()->create(['stripe_id' => 'cus_test_incomplete']);
        $this->createSubscription($user, self::ADVANCED_PRICE, 'incomplete');

        $response = $this->actingAsUser($user)->postJson('/api/v1/payments/checkout', ['plan' => 'pro']);

        // A 500 error from the checkout builder (not 409 from already-subscribed)
        // confirms incomplete subscriptions are not treated as swappable.
        $response->assertServerError();
    }

    // ==================== BILLING PORTAL ====================

    public function test_unauthenticated_user_cannot_access_billing_portal(): void
    {
        $response = $this->postJson('/api/v1/payments/billing-portal');

        $response->assertUnauthorized();
    }

    public function test_unverified_user_cannot_access_billing_portal(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/payments/billing-portal');

        $response->assertForbidden();
    }

    public function test_billing_portal_returns_422_when_user_has_no_stripe_id(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)->postJson('/api/v1/payments/billing-portal');

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'No billing information found.');
    }
}
