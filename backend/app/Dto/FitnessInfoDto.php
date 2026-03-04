<?php

declare(strict_types=1);

namespace App\Dto;

use App\Enums\ExperienceLevel;
use App\Enums\Gender;
use Spatie\LaravelData\Data;

final class FitnessInfoDto extends Data
{
    public function __construct(
        public readonly ?float $height = null,
        public readonly ?float $weight = null,
        public readonly ?int $age = null,
        public readonly ?Gender $gender = null,
        public readonly ?ExperienceLevel $experience_level = null,
    ) {}
}
