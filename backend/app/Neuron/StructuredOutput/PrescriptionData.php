<?php

declare(strict_types=1);

namespace App\Neuron\StructuredOutput;

use NeuronAI\StructuredOutput\SchemaProperty;

class PrescriptionData
{
    #[SchemaProperty(description: 'Order of this exercise within the block. Must be sequential starting from 1.', required: true)]
    public int $order;

    #[SchemaProperty(description: 'Number of sets. Required for resistance exercises; null only for pure cardio or mobility flows.', required: false)]
    public ?int $sets;

    #[SchemaProperty(description: 'Number of repetitions per set. Set to null for timed exercises (use duration_seconds instead).', required: false)]
    public ?int $reps;

    #[SchemaProperty(description: 'Load in kilograms. Set to null for bodyweight exercises.', required: false)]
    public ?float $weight;

    #[SchemaProperty(description: 'Duration in seconds for timed exercises (e.g. plank, cardio intervals). Set to null for rep-based exercises.', required: false)]
    public ?int $duration_seconds;

    #[SchemaProperty(description: 'Rest time in seconds between sets or after the exercise. Always provide a value.', required: true)]
    public int $rest_seconds;

    #[SchemaProperty(description: 'Rate of Perceived Exertion on a scale of 1 (very easy) to 10 (maximal effort). Calibrate to the user\'s goal and experience level.', required: true, min: 1, max: 10)]
    public float $rpe;
}
