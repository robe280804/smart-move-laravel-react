<?php

declare(strict_types=1);

namespace App\Neuron\StructuredOutput;

use NeuronAI\StructuredOutput\SchemaProperty;

class AdditionalMetricsData
{
    #[SchemaProperty(description: 'A concise overall description of the exercise: what it is, what it trains, and why it is included in this plan for this specific user.', required: false)]
    public ?string $description;

    #[SchemaProperty(description: 'Metabolic Equivalent of Task — estimated energy expenditure relative to rest. Typical range: 1.0 (rest) to 20.0 (sprinting).', required: false)]
    public ?float $met_value;

    #[SchemaProperty(description: 'Primary energy system predominantly used. Valid values: aerobic, anaerobic_lactic, anaerobic_alactic, mixed.', required: false)]
    public ?string $energy_system;

    #[SchemaProperty(description: 'Difficulty level of the exercise relative to the general population. Valid values: beginner, intermediate, advanced, professional.', required: false)]
    public ?string $difficulty;
}
