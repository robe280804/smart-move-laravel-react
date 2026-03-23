<?php

declare(strict_types=1);

namespace App\Events;

use App\Enums\SecurityEventType;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminAction
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $adminId,
        public readonly string $action,
        public readonly string $ip,
        public readonly ?int $targetUserId = null,
        public readonly string $details = '',
        public readonly SecurityEventType $type = SecurityEventType::AdminAction,
    ) {}
}
