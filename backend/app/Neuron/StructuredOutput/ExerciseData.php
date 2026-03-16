<?php

declare(strict_types=1);

namespace App\Neuron\StructuredOutput;

use NeuronAI\StructuredOutput\SchemaProperty;

class ExerciseData
{
    #[SchemaProperty(description: 'Full name of the exercise exactly as it appears in the knowledge base (e.g. "Barbell Back Squat", "Push-Up").', required: true)]
    public string $name;

    #[SchemaProperty(description: 'Exercise category. Examples: compound, isolation, plyometric, cardio, mobility, calisthenics.', required: true)]
    public string $category;

    #[SchemaProperty(description: 'Primary muscle group targeted (e.g. quadriceps, chest, hamstrings, back, shoulders, core).', required: false)]
    public ?string $muscle_group;

    #[SchemaProperty(description: 'Equipment needed for the exercise (e.g. barbell, dumbbell, resistance_band, bodyweight, cable_machine, kettlebell).', required: false)]
    public ?string $equipment;

    #[SchemaProperty(description: 'Step-by-step instructions to perform the exercise safely and correctly.', required: false)]
    public ?string $instructions;

    #[SchemaProperty(description: 'Coaching cues, common mistakes to avoid, or performance tips for the exercise.', required: false)]
    public ?string $infos;

    #[SchemaProperty(description: 'Additional performance metrics for the exercise.', required: false)]
    public ?AdditionalMetricsData $additional_metrics;

    #[SchemaProperty(description: 'Exercise prescription details including sets, reps, weight, and rest.', required: true)]
    public PrescriptionData $prescription;
}
