<?php

declare(strict_types=1);

namespace App\Neuron\Nodes;

use App\Neuron\Events\SanitizeInputEvent;
use App\Neuron\Events\UserInfosCollectedEvent;
use App\Repositories\Contracts\FitnessInfoRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use NeuronAI\Workflow\Node;
use NeuronAI\Workflow\WorkflowState;

class CollectUserInfosNode extends Node
{
    public function __construct(private readonly UserRepositoryInterface $repository) {}

    public function __invoke(SanitizeInputEvent $event, WorkflowState $state): UserInfosCollectedEvent
    {
        // Check if is present all the STEPS INFOS for generate the workoutplan
        // Otherwise return "I need also {information} for continue"

        // Get general fitness info about user 
        $fitnessInfo = $this->repository
            ->findById($state->get('user_id'))
            ->fitnessInfo()
            ->firstOrFail();

        $fitnessInfoPrompt = [
            'age' => $fitnessInfo->age,
            'height' => $fitnessInfo->height,
            'weight' => $fitnessInfo->weight,
            'gender' => $fitnessInfo->gender,
            'experience_level' => $fitnessInfo->experience_level,
        ];

        $state->set('fitness_data', $fitnessInfoPrompt);

        return new UserInfosCollectedEvent();
    }
}
