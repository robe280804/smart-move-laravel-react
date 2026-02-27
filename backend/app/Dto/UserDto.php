<?php

namespace App\Dto;

use Spatie\DataTransferObject\DataTransferObject;

final class UserDto extends DataTransferObject
{
    public function __construct(
        public readonly string $name,
        public readonly string $surname,
        public readonly string $email,
        public readonly ?string $password = null,
    ) {}
}
