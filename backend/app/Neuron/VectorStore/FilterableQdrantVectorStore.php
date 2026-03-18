<?php

declare(strict_types=1);

namespace App\Neuron\VectorStore;

use NeuronAI\HttpClient\HttpRequest;
use NeuronAI\RAG\Document;
use NeuronAI\RAG\VectorStore\QdrantVectorStore;

use function array_map;
use function in_array;

/**
 * Extends QdrantVectorStore to support an optional Qdrant payload filter.
 * When a filter is set, it is injected into the `points/query` request body,
 * narrowing results to vectors whose payload matches the given conditions.
 *
 * Typical usage — equipment OR filter:
 *
 *   $store = (new FilterableQdrantVectorStore(...))->withFilter([
 *       'should' => [
 *           ['key' => 'equipment', 'match' => ['value' => 'dumbbell']],
 *           ['key' => 'equipment', 'match' => ['value' => 'bodyweight']],
 *       ],
 *   ]);
 */
class FilterableQdrantVectorStore extends QdrantVectorStore
{
    /** @var array<string, mixed> */
    private array $filter = [];

    /**
     * Return a clone of this store with the given Qdrant filter applied.
     *
     * @param  array<string, mixed>  $filter
     */
    public function withFilter(array $filter): static
    {
        $clone = clone $this;
        $clone->filter = $filter;

        return $clone;
    }

    /**
     * {@inheritDoc}
     *
     * Overrides parent to inject the optional `filter` key into the Qdrant query body.
     *
     * @param  float[]  $embedding
     * @return Document[]
     */
    public function similaritySearch(array $embedding): iterable
    {
        $body = [
            'query' => [
                'recommend' => ['positive' => [$embedding]],
            ],
            'limit' => $this->topK,
            'with_payload' => true,
            'with_vector' => true,
        ];

        if ($this->filter !== []) {
            $body['filter'] = $this->filter;
        }

        $response = $this->httpClient->request(
            HttpRequest::post(uri: 'points/query', body: $body)
        )->json();

        return array_map(function (array $item): Document {
            $document = new Document($item['payload']['content']);
            $document->id = $item['id'];
            $document->embedding = $item['vector'];
            $document->sourceType = $item['payload']['sourceType'];
            $document->sourceName = $item['payload']['sourceName'];
            $document->score = $item['score'];

            foreach ($item['payload'] as $name => $value) {
                if (! in_array($name, ['content', 'sourceType', 'sourceName', 'score', 'embedding', 'id'])) {
                    $document->addMetadata($name, $value);
                }
            }

            return $document;
        }, $response['result']['points']);
    }
}
