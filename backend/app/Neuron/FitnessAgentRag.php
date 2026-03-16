<?php

declare(strict_types=1);

namespace App\Neuron;

use GuzzleHttp\HandlerStack;
use NeuronAI\Agent\SystemPrompt;
use NeuronAI\HttpClient\GuzzleHttpClient;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Anthropic\Anthropic;
use NeuronAI\Providers\Ollama\Ollama;
use NeuronAI\RAG\Embeddings\EmbeddingsProviderInterface;
use NeuronAI\RAG\Embeddings\OllamaEmbeddingsProvider;
use NeuronAI\RAG\RAG;
use NeuronAI\RAG\VectorStore\QdrantVectorStore;
use NeuronAI\RAG\VectorStore\VectorStoreInterface;

class FitnessAgentRag extends RAG
{
    protected function provider(): AIProviderInterface
    {
        return new Anthropic(
            key: config('services.claude.key'),
            model: config('services.claude.model'),
            max_tokens: 16000,
            httpClient: (new GuzzleHttpClient())->withTimeout(600.0),
        );
    }

    /*protected function instructions(): string
    {
        return (string) new SystemPrompt(
            background: ["You are a friendly AI Agent created with Neuron AI framework."],
        );
    }*/

    protected function embeddings(): EmbeddingsProviderInterface
    {
        return new OllamaEmbeddingsProvider(
            url: config('services.ollama.url'),
            model: 'nomic-embed-text'
        );
    }

    protected function vectorStore(): VectorStoreInterface
    {
        return new QdrantVectorStore(
            collectionUrl: config('services.qdrant.url'),
            key: config('services.qdrant.key'),
            topK: 30,
            dimension: 768,
        );
    }

    /**
     * @return ToolInterface[]|ToolkitInterface[]
     */
    protected function tools(): array
    {
        return [];
    }
}
