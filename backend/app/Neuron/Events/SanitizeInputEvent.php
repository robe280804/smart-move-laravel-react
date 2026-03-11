<?php

declare(strict_types=1);

namespace App\Neuron\Events;

use NeuronAI\Workflow\Events\Event;

class SanitizeInputEvent implements Event
{
    public function __construct(public readonly string $sanitizedMessage) {}
}
