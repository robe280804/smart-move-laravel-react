<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TokenAbility;
use App\Events\UserRegistration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private const VALID_PASSWORD = 'Password1!@#Test';

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            'api.pwnedpasswords.com/*' => Http::response('', 200),
        ]);

        Mail::fake();
    }

    // ==================== REGISTER ====================

    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john@example.com',
            'password' => self::VALID_PASSWORD,
            'password_confirmation' => self::VALID_PASSWORD,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'surname', 'email', 'role', 'created_at', 'updated_at'],
                ],
                'meta_data' => ['accessToken', 'accessTokenExpiresAt'],
            ])
            ->assertJsonPath('data.user.email', 'john@example.com')
            ->assertJsonPath('data.user.name', 'John')
            ->assertJsonPath('data.user.surname', 'Doe');
    }

    public function test_register_creates_user_in_database(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john@example.com',
            'password' => self::VALID_PASSWORD,
            'password_confirmation' => self::VALID_PASSWORD,
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john@example.com',
        ]);
    }

    public function test_register_fires_user_registration_event(): void
    {
        Event::fake([UserRegistration::class]);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john@example.com',
            'password' => self::VALID_PASSWORD,
            'password_confirmation' => self::VALID_PASSWORD,
        ]);

        Event::assertDispatched(UserRegistration::class, function (UserRegistration $event) {
            return $event->user->email === 'john@example.com';
        });
    }

    public function test_register_sets_http_only_refresh_token_cookie(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john@example.com',
            'password' => self::VALID_PASSWORD,
            'password_confirmation' => self::VALID_PASSWORD,
        ]);

        $response->assertCookie('refreshToken');
    }

    public function test_register_fails_without_name(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'surname' => 'Doe',
            'email' => 'john@example.com',
            'password' => self::VALID_PASSWORD,
            'password_confirmation' => self::VALID_PASSWORD,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_register_fails_without_surname(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John',
            'email' => 'john@example.com',
            'password' => self::VALID_PASSWORD,
            'password_confirmation' => self::VALID_PASSWORD,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['surname']);
    }

    public function test_register_fails_without_email(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John',
            'surname' => 'Doe',
            'password' => self::VALID_PASSWORD,
            'password_confirmation' => self::VALID_PASSWORD,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_fails_with_invalid_email_format(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'not-an-email',
            'password' => self::VALID_PASSWORD,
            'password_confirmation' => self::VALID_PASSWORD,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'john@example.com']);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john@example.com',
            'password' => self::VALID_PASSWORD,
            'password_confirmation' => self::VALID_PASSWORD,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_fails_without_password(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john@example.com',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_register_fails_without_password_confirmation(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john@example.com',
            'password' => self::VALID_PASSWORD,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_register_fails_with_mismatched_password_confirmation(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john@example.com',
            'password' => self::VALID_PASSWORD,
            'password_confirmation' => 'DifferentPass1!@#',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_register_fails_with_weak_password(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'weakpassword',
            'password_confirmation' => 'weakpassword',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    // ==================== LOGIN ====================

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create(['password' => bcrypt(self::VALID_PASSWORD)]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => self::VALID_PASSWORD,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'surname', 'email'],
                ],
                'meta_data' => ['accessToken', 'accessTokenExpiresAt'],
            ])
            ->assertJsonPath('data.user.email', $user->email);
    }

    public function test_login_sets_refresh_token_cookie(): void
    {
        $user = User::factory()->create(['password' => bcrypt(self::VALID_PASSWORD)]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => self::VALID_PASSWORD,
        ]);

        $response->assertCookie('refreshToken');
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt(self::VALID_PASSWORD)]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'WrongPass1!@#Test',
        ]);

        $response->assertUnauthorized();
    }

    public function test_login_fails_with_nonexistent_email(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'nobody@example.com',
            'password' => self::VALID_PASSWORD,
        ]);

        $response->assertUnauthorized();
    }

    public function test_login_fails_without_email(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'password' => self::VALID_PASSWORD,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_without_password(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    // ==================== REFRESH TOKEN ====================

    /*public function test_authenticated_user_can_refresh_token(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => self::VALID_PASSWORD,
        ]);

        $response = $this->postJson(route('refresh'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'accessToken',
                    'accessTokenExpiresAt'
                ]
            ]);

        $newAccessToken = $response->decodeResponseJson()['data']['accessToken'];
        $tokenHash = hash('sha256', $newAccessToken);
        $exists = PersonalAccessToken::where('token', $tokenHash)->exists();
        $this->assertTrue($exists);
    }*/

    /*public function test_refresh_token_deletes_old_tokens_and_issues_new_ones(): void
    {
        $user = User::factory()->create();

        $tokenResult = $user->createToken(
            'refresh-token',
            [TokenAbility::ISSUE_ACCESS_TOKEN->value],
            Carbon::now()->addMinutes(config('sanctum.rt_expiration'))
        );

        $user->refresh();
        $this->assertCount(1, $user->tokens);

        $this->withUnencryptedCookie('refreshToken', $tokenResult->plainTextToken)
            ->postJson('/api/v1/refresh-token');

        $this->assertEquals(2, $user->tokens()->count());
    }*/

    public function test_unauthenticated_user_cannot_refresh_token(): void
    {
        $response = $this->postJson('/api/v1/refresh-token');

        $response->assertUnauthorized();
    }

    public function test_expired_refresh_token_cannot_be_used(): void
    {
        $user = User::factory()->create();

        $tokenResult = $user->createToken(
            'refresh-token',
            [TokenAbility::ISSUE_ACCESS_TOKEN->value],
            Carbon::now()->subMinute()
        );

        $response = $this->withUnencryptedCookie('refreshToken', $tokenResult->plainTextToken)
            ->postJson('/api/v1/refresh-token');

        $response->assertUnauthorized();
    }

    // ==================== VERIFY EMAIL ====================

    public function test_user_can_verify_email_with_valid_signed_url(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => hash('sha256', $user->email)]
        );

        $response = $this->getJson($url);

        $response->assertOk()
            ->assertJsonPath('meta_data.message', 'Email successfully verified.');

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_already_verified_email_returns_appropriate_message(): void
    {
        $user = User::factory()->create();

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => hash('sha256', $user->email)]
        );

        $response = $this->getJson($url);

        $response->assertOk()
            ->assertJsonPath('meta_data.message', 'Email already verified.');
    }

    public function test_verify_email_fails_without_valid_signature(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->getJson("/api/v1/auth/email/verify/{$user->id}/fakehash");

        $response->assertStatus(403);
    }

    public function test_verify_email_fails_with_expired_signature(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->subMinute(),
            ['id' => $user->id, 'hash' => hash('sha256', $user->email)]
        );

        $response = $this->getJson($url);

        $response->assertStatus(403);
    }

    public function test_verify_email_fails_with_nonexistent_user(): void
    {
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => 99999, 'hash' => 'somehash']
        );

        $response = $this->getJson($url);

        $response->assertNotFound();
    }
}
