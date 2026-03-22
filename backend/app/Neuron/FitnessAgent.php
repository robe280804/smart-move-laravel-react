<?php

declare(strict_types=1);

namespace App\Neuron;

use App\Neuron\Prompts\BackgroundPrompt;
use App\Neuron\Prompts\OutputPrompt;
use App\Neuron\Prompts\SecurityPrompt;
use App\Neuron\Prompts\StepsPrompt;
use NeuronAI\Agent\Agent;
use NeuronAI\Agent\SystemPrompt;
use NeuronAI\HttpClient\GuzzleHttpClient;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Anthropic\Anthropic;
use NeuronAI\Tools\ToolInterface;
use NeuronAI\Tools\Toolkits\ToolkitInterface;

class FitnessAgent extends Agent
{
    protected function provider(): AIProviderInterface
    {

        return new Anthropic(
            key: config('services.claude.key'),
            model: config('services.claude.model'),
            max_tokens: 16000,
            parameters: ['temperature' => 0.4],
            httpClient: (new GuzzleHttpClient)->withTimeout(600.0),
        );
    }

    protected function instructions(): string
    {
        return (string) new SystemPrompt(
            background: array_merge(
                SecurityPrompt::content(),
                BackgroundPrompt::content(),
            ),
            steps: StepsPrompt::content(),
            output: OutputPrompt::content()
        );
    }

    /**
     * @return ToolInterface[]|ToolkitInterface[]
     */
    protected function tools(): array
    {
        return [];
    }

    protected function middleware(): array
    {
        return [];
    }
}
