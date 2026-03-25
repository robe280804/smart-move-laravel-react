<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\SecurityEventType;
use App\Events\AdminAction;
use App\Events\FailedLogin;
use App\Events\SecurityAlert;
use App\Notifications\SecurityAlertNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class HandleSecurityEvent implements ShouldQueue
{
    public function handle(FailedLogin|SecurityAlert|AdminAction $event): void
    {
        $context = $this->buildContext($event);

        Log::channel('security')->{$context['level']}(
            sprintf(
                '[SECURITY] %s | %s | IP: %s | User: %s | %s',
                strtoupper($context['level']),
                $context['type']->value,
                $context['ip'],
                $context['userId'] ?? 'guest',
                $context['details'],
            )
        );

        $this->notifyIfCritical($context['type'], $context['details']);
    }

    /**
     * @return array{level: string, type: SecurityEventType, ip: string, userId: int|null, details: string}
     */
    private function buildContext(FailedLogin|SecurityAlert|AdminAction $event): array
    {
        return match (true) {
            $event instanceof FailedLogin => [
                'level' => 'warning',
                'type' => $event->type,
                'ip' => $event->ip,
                'userId' => null,
                'details' => "Email: {$event->email}",
            ],
            $event instanceof AdminAction => [
                'level' => 'info',
                'type' => $event->type,
                'ip' => $event->ip,
                'userId' => $event->adminId,
                'details' => "Action: {$event->action} | Target: {$event->targetUserId} | {$event->details}",
            ],
            $event instanceof SecurityAlert => [
                'level' => $this->levelForType($event->type),
                'type' => $event->type,
                'ip' => $event->ip,
                'userId' => $event->userId,
                'details' => $event->details,
            ],
        };
    }

    private function levelForType(SecurityEventType $type): string
    {
        return match ($type) {
            SecurityEventType::ForbiddenAccess => 'warning',
            SecurityEventType::UnhandledException => 'error',
            SecurityEventType::AiGenerationFailure => 'error',
            SecurityEventType::AiCreditsExhausted => 'error',
            SecurityEventType::AccountDeletion => 'warning',
            default => 'info',
        };
    }

    private function notifyIfCritical(SecurityEventType $type, string $details): void
    {
        $adminEmail = config('app.admin_email');

        if (empty($adminEmail)) {
            return;
        }

        $criticalTypes = [
            SecurityEventType::UnhandledException,
            SecurityEventType::ForbiddenAccess,
            SecurityEventType::AiGenerationFailure,
            SecurityEventType::AiCreditsExhausted,
        ];

        if (! in_array($type, $criticalTypes, true)) {
            return;
        }

        // Throttle: max 1 email per event type per 30 minutes
        $cacheKey = 'security_alert_throttle:'.$type->value;

        if (Cache::add($cacheKey, true, 30 * 60)) {
            Notification::route('mail', $adminEmail)
                ->notify(new SecurityAlertNotification($type, $details));
        }
    }
}
