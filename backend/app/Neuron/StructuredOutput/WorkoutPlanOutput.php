<?php

declare(strict_types=1);

namespace App\Neuron\StructuredOutput;

use NeuronAI\StructuredOutput\SchemaProperty;

class WorkoutPlanOutput
{
    #[SchemaProperty(description: 'The complete generated workout plan.', required: true)]
    public WorkoutPlanData $workout_plan;
}
