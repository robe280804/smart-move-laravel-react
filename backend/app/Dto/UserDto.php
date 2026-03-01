<?php

namespace App\Dto;

use Spatie\LaravelData\Data;

final class UserDto extends Data
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $surname = null,
        public readonly ?string $email = null,
        public readonly ?string $password = null,
    ) {}
}
