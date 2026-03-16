<?php

declare(strict_types=1);

namespace App\Neuron\StructuredOutput;

use NeuronAI\StructuredOutput\SchemaProperty;

class WorkoutPlanData
{
    #[SchemaProperty(description: 'Number of training days per week.', required: true, min: 1, max: 7)]
    public int $training_days_per_week;

    #[SchemaProperty(description: 'Primary training goal. Valid values: weight_loss, muscle_gain, strength_building, endurance, flexibility, general_fitness, body_recomposition, athletic_performance, rehabilitation, posture_correction, functional_fitness.', required: true)]
    public string $goal;

    #[SchemaProperty(description: 'User experience level. Valid values: beginner, intermediate, advanced, professional.', required: true)]
    public string $experience_level;

    #[SchemaProperty(description: 'Dominant workout type for the plan. Valid values: strength, cardio, mobility, conditioning, hiit, bodyweight, functional, core, recovery.', required: true)]
    public string $workout_type;

    #[SchemaProperty(description: 'List of training days in the plan.', required: true, anyOf: [PlanDayData::class])]
    public array $plan_days;
}
