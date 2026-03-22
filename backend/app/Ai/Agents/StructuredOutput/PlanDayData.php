<?php

declare(strict_types=1);

namespace App\Ai\Agents\StructuredOutput;

class PlanDayData
{
    public int $day_of_week;

    public ?string $workout_name;

    public int $duration_minutes;

    /** @var WorkoutBlockData[] */
    public array $workout_blocks;

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $obj = new self;
        $obj->day_of_week = (int) $data['day_of_week'];
        $obj->workout_name = $data['workout_name'] ?? null;
        $obj->duration_minutes = (int) $data['duration_minutes'];
        $obj->workout_blocks = array_map(
            fn (array $b) => WorkoutBlockData::fromArray($b),
            $data['workout_blocks'] ?? [],
        );

        return $obj;
    }
}
