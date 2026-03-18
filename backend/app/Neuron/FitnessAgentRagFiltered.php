<?php

declare(strict_types=1);

namespace App\Neuron;

use App\Neuron\VectorStore\FilterableQdrantVectorStore;
use NeuronAI\RAG\VectorStore\VectorStoreInterface;

/**
 * A RAG agent variant that narrows Qdrant vector searches to a specific
 * equipment filter. Extend FitnessAgentRag so all provider/embedding
 * configuration is inherited; only the vector store is swapped.
 *
 * Usage inside a Concurrency::run() closure:
 *
 *   fn(): array => FitnessAgentRagFiltered::make()
 *       ->setEquipmentFilter($filter)
 *       ->resolveRetrieval()
 *       ->retrieve(new UserMessage($query))
 *
 * The filter must be set BEFORE resolveRetrieval() is called, because
 * ResolveVectorStore caches the store instance on first access.
 */
class FitnessAgentRagFiltered extends FitnessAgentRag
{
    /** @var array<string, mixed> */
    private array $equipmentFilter = [];

    /**
     * Store the Qdrant filter to apply on the next resolveRetrieval() call.
     *
     * @param  array<string, mixed>  $filter
     */
    public function setEquipmentFilter(array $filter): static
    {
        $this->equipmentFilter = $filter;

        return $this;
    }

    /**
     * Override to return a FilterableQdrantVectorStore with the equipment
     * filter applied when one has been provided.
     */
    protected function vectorStore(): VectorStoreInterface
    {
        $store = new FilterableQdrantVectorStore(
            collectionUrl: config('services.qdrant.url'),
            key: config('services.qdrant.key'),
            topK: 15,
            dimension: 768,
        );

        if ($this->equipmentFilter !== []) {
            return $store->withFilter($this->equipmentFilter);
        }

        return $store;
    }
}
