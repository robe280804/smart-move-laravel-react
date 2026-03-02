<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiSuccess implements Responsable
{
    public function __construct(
        private mixed $data,
        private array $metaData,
        private int $statusCode = Response::HTTP_CREATED,
        private array $headers = [],
        private int $options = 0
    ) {}

    public function toResponse($request): JsonResponse
    {
        return response()->json(
            [
                'data' => $this->data,
                'metaData' => $this->metaData
            ],
            $this->statusCode,
            $this->headers,
            $this->options
        );
    }
}
