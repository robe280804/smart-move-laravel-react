<?php

declare(strict_types=1);

namespace App\Dto;

use Spatie\LaravelData\Data;

final class FeedbackDto extends Data
{
    public function __construct(
        public readonly ?int $rating = null,
        public readonly ?string $message = null,
    ) {}
}
