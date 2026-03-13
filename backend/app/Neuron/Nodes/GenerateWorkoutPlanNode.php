<?php

declare(strict_types=1);

namespace App\Neuron\Nodes;

use App\Neuron\Events\UserInfosCollectedEvent;
use App\Neuron\FitnessAgent;
use App\Neuron\FitnessAgentRag;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\RAG\Document;
use NeuronAI\Workflow\Events\StopEvent;
use NeuronAI\Workflow\Node;
use NeuronAI\Workflow\WorkflowState;

class GenerateWorkoutPlanNode extends Node
{
    private const TOP_K_EXERCISES = 20;

    public function __invoke(UserInfosCollectedEvent $event, WorkflowState $state): StopEvent
    {
        /** @var array<string, mixed> $fitnessData */
        $fitnessData = (array) $state->get('fitness_data', []);

        $documents = FitnessAgentRag::make()
            ->resolveRetrieval()
            ->retrieve(new UserMessage($this->buildRetrievalQuery($fitnessData)));

        $prompt = $this->buildPrompt(
            $fitnessData,
            $this->formatExerciseContext($documents),
            (string) $state->get('user_message', ''),
        );

        $response = FitnessAgent::make()
            ->chat(new UserMessage($prompt))
            ->getMessage();

        $state->set('agent_response', $response->getContent());

        return new StopEvent();
    }

    /**
     * Build a retrieval query from the collected fitness profile.
     *
     * @param array<string, mixed> $fitnessData
     */
    private function buildRetrievalQuery(array $fitnessData): string
    {
        $parts = array_filter([
            $fitnessData['experience_level'] ?? null,
            $fitnessData['gender'] ?? null,
            $fitnessData['height'] ?? null,
            $fitnessData['weight'] ?? null,
            $fitnessData['age'] ?? null,
            'fitness workout exercises',
        ]);

        return implode(' ', $parts);
    }

    /**
     * Format retrieved documents into an exercise context block.
     *
     * @param Document[] $documents
     */
    private function formatExerciseContext(array $documents): string
    {
        if ($documents === []) {
            return '';
        }

        $lines = ['=== AVAILABLE EXERCISES FROM KNOWLEDGE BASE ==='];

        foreach (array_slice($documents, 0, self::TOP_K_EXERCISES) as $document) {
            $lines[] = '- ' . $document->getContent();
        }

        return implode("\n", $lines);
    }

    /**
     * Assemble the full prompt for FitnessAgent.
     *
     * @param array<string, mixed> $fitnessData
     */
    private function buildPrompt(array $fitnessData, string $exerciseContext, string $userMessage): string
    {
        $profileLines = [];

        foreach ($fitnessData as $key => $value) {
            $formatted = is_array($value) ? implode(', ', $value) : (string) $value;
            $profileLines[] = sprintf('  %s: %s', str_replace('_', ' ', (string) $key), $formatted);
        }

        $sections = [
            '=== USER FITNESS PROFILE ===',
            implode("\n", $profileLines),
        ];

        if ($exerciseContext !== '') {
            $sections[] = '';
            $sections[] = $exerciseContext;
        }

        if ($userMessage !== '') {
            $sections[] = '';
            $sections[] = '=== USER REQUEST ===';
            $sections[] = $userMessage;
        }

        $sections[] = '';
        $sections[] = 'Using the fitness profile above and ONLY the exercises listed in the knowledge base section, generate the complete workout plan as a single JSON object.';

        return implode("\n", $sections);
    }
}
