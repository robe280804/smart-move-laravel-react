<?php

declare(strict_types=1);

namespace App\Neuron\StructuredOutput;

use App\Enums\ExperienceLevel;
use App\Enums\TrainingGoalType;
use App\Enums\WorkoutType;
use NeuronAI\StructuredOutput\SchemaProperty;

class WorkoutPlanData
{
    #[SchemaProperty(description: 'Number of training days per week.', required: true, min: 1, max: 7)]
    public int $training_days_per_week;

    #[SchemaProperty(description: 'Primary training goal.', required: true)]
    public TrainingGoalType $goal;

    #[SchemaProperty(description: 'User experience level.', required: true)]
    public ExperienceLevel $experience_level;

    #[SchemaProperty(description: 'Dominant workout type for the plan.', required: true)]
    public WorkoutType $workout_type;

    #[SchemaProperty(description: 'List of training days in the plan.', required: true, anyOf: [PlanDayData::class])]
    public array $plan_days;
}
