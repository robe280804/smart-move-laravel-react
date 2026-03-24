<?php

declare(strict_types=1);

namespace App\Enums;

enum SubscriptionPlan: string
{
    case Free = 'free';
    case Advanced = 'advanced';
    case Pro = 'pro';

    public function generationLimit(): ?int
    {
        return match ($this) {
            self::Free => 1,
            self::Advanced => 10,
            self::Pro => 20,
        };
    }

    public function activePlansLimit(): ?int
    {
        return match ($this) {
            self::Free => 1,
            self::Advanced => 10,
            self::Pro => null,
        };
    }

    public function historyDaysLimit(): ?int
    {
        return match ($this) {
            self::Free => 30,
            self::Advanced => null,
            self::Pro => null,
        };
    }

    public function canExportPdf(): bool
    {
        return $this !== self::Free;
    }

    public function canEditExercises(): bool
    {
        return $this !== self::Free;
    }

    public function hasPriorityGeneration(): bool
    {
        return $this === self::Pro;
    }
}
