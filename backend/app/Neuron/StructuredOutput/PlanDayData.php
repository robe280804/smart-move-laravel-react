<?php

declare(strict_types=1);

namespace App\Neuron\StructuredOutput;

use NeuronAI\StructuredOutput\SchemaProperty;

class PlanDayData
{
    #[SchemaProperty(description: 'Day of the week as an integer (1=Monday, 7=Sunday).', required: true)]
    public int $day_of_week;

    #[SchemaProperty(description: 'Name of the workout session (e.g. Upper Body, Leg Day).', required: false)]
    public ?string $workout_name;

    #[SchemaProperty(description: 'Total session duration in minutes.', required: true)]
    public int $duration_minutes;

    #[SchemaProperty(description: 'List of workout blocks for this day. Must include at least a Warmup (order 1) and a Cool-down as the last block.', required: true, anyOf: [WorkoutBlockData::class])]
    public array $workout_blocks;
}
