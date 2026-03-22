<?php

declare(strict_types=1);

namespace App\Neuron\Nodes;

use App\Neuron\Events\SanitizeInputEvent;
use App\Neuron\Events\UserInfosCollectedEvent;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use NeuronAI\Workflow\Node;
use NeuronAI\Workflow\WorkflowState;

class CollectUserInfosNode extends Node
{
    public function __construct(private readonly UserRepositoryInterface $repository) {}

    public function __invoke(SanitizeInputEvent $event, WorkflowState $state): UserInfosCollectedEvent
    {
        $userId = (int) $state->get('user_id');

        // Get the user fitness info by cache or if not present by db
        $fitnessInfoPrompt = Cache::remember(
            "fitness_profile:{$userId}",
            now()->addMinutes(10),
            function () use ($userId): array {
                $fitnessInfo = $this->repository
                    ->findById($userId)
                    ->fitnessInfo()
                    ->firstOrFail();

                return [
                    'age' => $fitnessInfo->age,
                    'height' => $fitnessInfo->height,
                    'weight' => $fitnessInfo->weight,
                    'gender' => $fitnessInfo->gender,
                    'experience_level' => $fitnessInfo->experience_level,
                ];
            },
        );

        $state->set('fitness_data', $fitnessInfoPrompt);

        return new UserInfosCollectedEvent;
    }
}
