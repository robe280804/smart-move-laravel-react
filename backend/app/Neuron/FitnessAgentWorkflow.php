<?php

declare(strict_types=1);

namespace App\Neuron;

use NeuronAI\Workflow\Workflow;
use App\Neuron\Nodes\InitialNode;

class FitnessAgentWorkflow extends Workflow
{

    /** My Workflow setup
     * 
     * 1. InitialNode (User message + get infos from db + get all the infos)   
     */


    protected function nodes(): array
    {
        return [
            new InitialNode(),
        ];
    }
}
