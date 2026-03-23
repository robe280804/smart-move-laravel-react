<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\SecurityEventType;
use App\Enums\TokenAbility;
use App\Enums\WorkoutPlanStatus;
use App\Events\AdminAction;
use App\Events\FailedLogin;
use App\Events\SecurityAlert;
use App\Jobs\GenerateWorkoutPlanJob;
use App\Listeners\HandleSecurityEvent;
use App\Models\User;
use App\Models\WorkoutPlan;
use App\Notifications\SecurityAlertNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SecurityEventTest extends TestCase
{
    use RefreshDatabase;

    private const VALID_PASSWORD = 'Password1!@#Test';

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake(['*pwnedpasswords.com/*' => Http::response('', 200)]);
        Mail::fake();
    }

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

    // ==================== FAILED LOGIN DISPATCH ====================

    public function test_failed_login_dispatches_event(): void
    {
        Event::fake([FailedLogin::class]);

        User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt(self::VALID_PASSWORD),
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'WrongPassword!123',
        ]);

        Event::assertDispatched(FailedLogin::class, function (FailedLogin $event) {
            return $event->email === 'user@example.com'
                && $event->type === SecurityEventType::FailedLogin;
        });
    }

    public function test_successful_login_does_not_dispatch_failed_login_event(): void
    {
        Event::fake([FailedLogin::class]);

        User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt(self::VALID_PASSWORD),
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => self::VALID_PASSWORD,
        ]);

        Event::assertNotDispatched(FailedLogin::class);
    }

    public function test_failed_login_with_nonexistent_email_dispatches_event(): void
    {
        Event::fake([FailedLogin::class]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'nobody@example.com',
            'password' => 'AnyPassword!123',
        ]);

        Event::assertDispatched(FailedLogin::class, function (FailedLogin $event) {
            return $event->email === 'nobody@example.com';
        });
    }

    // ==================== PASSWORD CHANGE DISPATCH ====================

    public function test_password_change_dispatches_security_alert(): void
    {
        Event::fake([SecurityAlert::class]);

        $user = User::factory()->create([
            'password' => bcrypt(self::VALID_PASSWORD),
        ]);

        $this->actingAsUser($user)->postJson('/api/v1/users/change-password', [
            'current_password' => self::VALID_PASSWORD,
            'password' => 'NewSecure@456',
            'password_confirmation' => 'NewSecure@456',
        ]);

        Event::assertDispatched(SecurityAlert::class, function (SecurityAlert $event) use ($user) {
            return $event->type === SecurityEventType::PasswordChange
                && $event->userId === $user->id;
        });
    }

    // ==================== ACCOUNT DELETION DISPATCH ====================

    public function test_account_deletion_dispatches_security_alert(): void
    {
        Event::fake([SecurityAlert::class]);

        $user = User::factory()->create();

        $this->actingAsUser($user)->deleteJson("/api/v1/users/{$user->id}");

        Event::assertDispatched(SecurityAlert::class, function (SecurityAlert $event) use ($user) {
            return $event->type === SecurityEventType::AccountDeletion
                && $event->userId === $user->id;
        });
    }

    // ==================== ADMIN ACTION DISPATCH ====================

    public function test_admin_update_dispatches_admin_action_event(): void
    {
        Event::fake([AdminAction::class]);

        $admin = $this->createAdmin();
        $user = User::factory()->create();

        $this->actingAsUser($admin)->putJson("/api/v1/admin/users/{$user->id}", [
            'name' => 'Updated',
        ]);

        Event::assertDispatched(AdminAction::class, function (AdminAction $event) use ($admin, $user) {
            return $event->adminId === $admin->id
                && $event->targetUserId === $user->id
                && $event->action === 'update_user';
        });
    }

    public function test_admin_delete_dispatches_admin_action_event(): void
    {
        Event::fake([AdminAction::class]);

        $admin = $this->createAdmin();
        $user = User::factory()->create();

        $this->actingAsUser($admin)->deleteJson("/api/v1/admin/users/{$user->id}");

        Event::assertDispatched(AdminAction::class, function (AdminAction $event) use ($admin, $user) {
            return $event->adminId === $admin->id
                && $event->targetUserId === $user->id
                && $event->action === 'delete_user';
        });
    }

    // ==================== AI GENERATION FAILURE DISPATCH ====================

    public function test_ai_generation_failure_dispatches_security_alert(): void
    {
        Event::fake([SecurityAlert::class]);

        $user = User::factory()->create();
        $plan = WorkoutPlan::factory()->create([
            'user_id' => $user->id,
            'status' => WorkoutPlanStatus::Pending,
        ]);

        $job = new GenerateWorkoutPlanJob($plan, $user, []);
        $job->failed(new \RuntimeException('Anthropic API timeout'));

        Event::assertDispatched(SecurityAlert::class, function (SecurityAlert $event) use ($user, $plan) {
            return $event->type === SecurityEventType::AiGenerationFailure
                && $event->userId === $user->id
                && str_contains($event->details, (string) $plan->id);
        });
    }

    // ==================== LISTENER — LOGGING ====================

    public function test_listener_logs_failed_login_to_security_channel(): void
    {
        Log::shouldReceive('channel')
            ->with('security')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('warning')
            ->once()
            ->withArgs(fn (string $msg) => str_contains($msg, '[SECURITY]')
                && str_contains($msg, 'failed_login')
                && str_contains($msg, 'attacker@example.com'));

        $event = new FailedLogin(
            email: 'attacker@example.com',
            ip: '192.168.1.100',
        );

        $listener = new HandleSecurityEvent;
        $listener->handle($event);
    }

    public function test_listener_logs_admin_action_to_security_channel(): void
    {
        Log::shouldReceive('channel')
            ->with('security')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(fn (string $msg) => str_contains($msg, '[SECURITY]')
                && str_contains($msg, 'admin_action')
                && str_contains($msg, 'delete_user'));

        $event = new AdminAction(
            adminId: 1,
            action: 'delete_user',
            ip: '10.0.0.1',
            targetUserId: 99,
            details: 'Admin deleted user.',
        );

        $listener = new HandleSecurityEvent;
        $listener->handle($event);
    }

    public function test_listener_logs_security_alert_to_security_channel(): void
    {
        Log::shouldReceive('channel')
            ->with('security')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('error')
            ->once()
            ->withArgs(fn (string $msg) => str_contains($msg, '[SECURITY]')
                && str_contains($msg, 'ai_generation_failure'));

        $event = new SecurityAlert(
            type: SecurityEventType::AiGenerationFailure,
            ip: 'queue',
            userId: 5,
            details: 'Plan 42 failed.',
        );

        $listener = new HandleSecurityEvent;
        $listener->handle($event);
    }

    // ==================== LISTENER — EMAIL NOTIFICATIONS ====================

    public function test_critical_event_sends_notification_to_admin(): void
    {
        Notification::fake();
        Cache::flush();
        config(['app.admin_email' => 'admin@example.com']);

        $event = new SecurityAlert(
            type: SecurityEventType::UnhandledException,
            ip: '10.0.0.1',
            userId: null,
            details: 'RuntimeException: something broke',
        );

        $listener = new HandleSecurityEvent;
        $listener->handle($event);

        Notification::assertSentOnDemand(SecurityAlertNotification::class, function ($notification, $channels, $notifiable) {
            return $notification->eventType === SecurityEventType::UnhandledException
                && $notifiable->routes['mail'] === 'admin@example.com';
        });
    }

    public function test_notification_is_throttled_within_30_minutes(): void
    {
        Notification::fake();
        Cache::flush();
        config(['app.admin_email' => 'admin@example.com']);

        $event = new SecurityAlert(
            type: SecurityEventType::AiGenerationFailure,
            ip: 'queue',
            userId: 1,
            details: 'First failure',
        );

        $listener = new HandleSecurityEvent;
        $listener->handle($event);
        $listener->handle($event);
        $listener->handle($event);

        Notification::assertSentOnDemandTimes(SecurityAlertNotification::class, 1);
    }

    public function test_different_critical_types_are_throttled_independently(): void
    {
        Notification::fake();
        Cache::flush();
        config(['app.admin_email' => 'admin@example.com']);

        $listener = new HandleSecurityEvent;

        $listener->handle(new SecurityAlert(
            type: SecurityEventType::UnhandledException,
            ip: '10.0.0.1',
            details: 'Error 1',
        ));

        $listener->handle(new SecurityAlert(
            type: SecurityEventType::AiGenerationFailure,
            ip: 'queue',
            userId: 1,
            details: 'Error 2',
        ));

        Notification::assertSentOnDemandTimes(SecurityAlertNotification::class, 2);
    }

    public function test_no_notification_when_admin_email_not_configured(): void
    {
        Notification::fake();
        Cache::flush();
        config(['app.admin_email' => null]);

        $event = new SecurityAlert(
            type: SecurityEventType::UnhandledException,
            ip: '10.0.0.1',
            details: 'Something broke',
        );

        $listener = new HandleSecurityEvent;
        $listener->handle($event);

        Notification::assertSentOnDemandTimes(SecurityAlertNotification::class, 0);
    }

    public function test_non_critical_event_does_not_send_notification(): void
    {
        Notification::fake();
        Cache::flush();
        config(['app.admin_email' => 'admin@example.com']);

        $event = new SecurityAlert(
            type: SecurityEventType::PasswordChange,
            ip: '10.0.0.1',
            userId: 1,
            details: 'User changed password.',
        );

        $listener = new HandleSecurityEvent;
        $listener->handle($event);

        Notification::assertSentOnDemandTimes(SecurityAlertNotification::class, 0);
    }

    public function test_failed_login_event_does_not_send_notification(): void
    {
        Notification::fake();
        Cache::flush();
        config(['app.admin_email' => 'admin@example.com']);

        $event = new FailedLogin(
            email: 'test@example.com',
            ip: '10.0.0.1',
        );

        $listener = new HandleSecurityEvent;
        $listener->handle($event);

        Notification::assertSentOnDemandTimes(SecurityAlertNotification::class, 0);
    }
}
