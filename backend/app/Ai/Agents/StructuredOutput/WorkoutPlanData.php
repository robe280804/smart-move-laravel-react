<?php

declare(strict_types=1);

namespace App\Ai\Agents\StructuredOutput;

use App\Enums\ExperienceLevel;
use App\Enums\TrainingGoalType;
use App\Enums\WorkoutType;

class WorkoutPlanData
{
    public int $training_days_per_week;

    public TrainingGoalType $goal;

    public ExperienceLevel $experience_level;

    public WorkoutType $workout_type;

    /** @var PlanDayData[] */
    public array $plan_days;

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $obj = new self;
        $obj->training_days_per_week = (int) $data['training_days_per_week'];
        $obj->goal = TrainingGoalType::from((string) $data['goal']);
        $obj->experience_level = ExperienceLevel::from((string) $data['experience_level']);
        $obj->workout_type = WorkoutType::from((string) $data['workout_type']);
        $obj->plan_days = array_map(
            fn (array $d) => PlanDayData::fromArray($d),
            $data['plan_days'] ?? [],
        );

        return $obj;
    }
}
