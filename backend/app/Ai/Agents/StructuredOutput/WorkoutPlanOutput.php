<?php

declare(strict_types=1);

namespace App\Ai\Agents\StructuredOutput;

class WorkoutPlanOutput
{
    public WorkoutPlanData $workout_plan;

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $obj = new self;
        $obj->workout_plan = WorkoutPlanData::fromArray($data['workout_plan']);

        return $obj;
    }
}
