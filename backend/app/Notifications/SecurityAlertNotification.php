<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\SecurityEventType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SecurityAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly SecurityEventType $eventType,
        public readonly string $details,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[SECURITY] '.config('app.name').' — '.$this->eventType->value)
            ->line('A security event has been detected:')
            ->line('**Event type:** '.$this->eventType->value)
            ->line('**Details:** '.$this->details)
            ->line('**Time:** '.now()->toDateTimeString())
            ->line('Please investigate immediately if this is unexpected.');
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'event_type' => $this->eventType->value,
            'details' => $this->details,
        ];
    }
}
