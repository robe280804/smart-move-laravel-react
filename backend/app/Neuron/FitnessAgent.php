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
use NeuronAI\Providers\Anthropic;
use NeuronAI\Providers\Ollama\Ollama;
use NeuronAI\Tools\ToolInterface;
use NeuronAI\Tools\Toolkits\ToolkitInterface;

class FitnessAgent extends Agent
{
    protected function provider(): AIProviderInterface
    {
        // return an instance of Anthropic, OpenAI, Gemini, Ollama, etc...
        // https://docs.neuron-ai.dev/providers/ai-provider
        return new Ollama(
            url: config('services.ollama.url'),
            model: config('services.ollama.model'),
            httpClient: (new GuzzleHttpClient())->withTimeout(300.0),
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

    /**
     * Attach middleware to nodes.
     */
    protected function middleware(): array
    {
        return [
            // ToolNode::class => [],
        ];
    }
}
