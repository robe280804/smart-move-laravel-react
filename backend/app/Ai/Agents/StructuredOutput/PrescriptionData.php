<?php

declare(strict_types=1);

namespace App\Ai\Agents\StructuredOutput;

class PrescriptionData
{
    public int $order;

    public ?int $sets;

    public ?int $reps;

    public ?float $weight;

    public ?int $duration_seconds;

    public int $rest_seconds;

    public float $rpe;

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $obj = new self;
        $obj->order = (int) $data['order'];
        $obj->sets = isset($data['sets']) ? (int) $data['sets'] : null;
        $obj->reps = isset($data['reps']) ? (int) $data['reps'] : null;
        $obj->weight = isset($data['weight']) ? (float) $data['weight'] : null;
        $obj->duration_seconds = isset($data['duration_seconds']) ? (int) $data['duration_seconds'] : null;
        $obj->rest_seconds = (int) ($data['rest_seconds'] ?? 60);
        $obj->rpe = (float) ($data['rpe'] ?? 7.0);

        return $obj;
    }
}
