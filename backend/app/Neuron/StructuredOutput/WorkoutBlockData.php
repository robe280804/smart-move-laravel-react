<?php

declare(strict_types=1);

namespace App\Neuron\StructuredOutput;

use NeuronAI\StructuredOutput\SchemaProperty;

class WorkoutBlockData
{
    #[SchemaProperty(description: 'Name of the block (e.g. Warmup, Main, Cool-down).', required: true)]
    public string $name;

    #[SchemaProperty(description: 'Order of this block within the training day.', required: true)]
    public int $order;

    #[SchemaProperty(description: 'List of exercises in this block.', required: true, anyOf: [ExerciseData::class])]
    public array $exercises;
}
