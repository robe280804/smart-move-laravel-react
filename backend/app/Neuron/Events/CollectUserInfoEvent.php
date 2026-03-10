<?php

declare(strict_types=1);

namespace App\Neuron\Event;

use NeuronAI\Workflow\Events\Event;

class CollectUserInfoEvent implements Event
{
    /**
     * Add class properties to carry custom data.
     */
    public function __construct() {}
}
