<?php

declare(strict_types=1);

namespace App\Events;

use App\Enums\SecurityEventType;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FailedLogin
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $email,
        public readonly string $ip,
        public readonly SecurityEventType $type = SecurityEventType::FailedLogin,
    ) {}
}
