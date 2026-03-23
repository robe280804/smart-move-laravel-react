<?php

declare(strict_types=1);

namespace App\Events;

use App\Enums\SecurityEventType;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SecurityAlert
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly SecurityEventType $type,
        public readonly string $ip,
        public readonly ?int $userId = null,
        public readonly string $details = '',
    ) {}
}
