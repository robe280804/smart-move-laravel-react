<?php

declare(strict_types=1);

namespace App\Ai\Agents\StructuredOutput;

class ExerciseData
{
    public string $name;

    public string $category;

    public ?string $muscle_group;

    public ?string $equipment;

    public ?string $instructions;

    public ?string $infos;

    public ?AdditionalMetricsData $additional_metrics;

    public PrescriptionData $prescription;

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $obj = new self;
        $obj->name = (string) $data['name'];
        $obj->category = (string) $data['category'];
        $obj->muscle_group = $data['muscle_group'] ?? null;
        $obj->equipment = $data['equipment'] ?? null;
        $obj->instructions = $data['instructions'] ?? null;
        $obj->infos = $data['infos'] ?? null;
        $obj->additional_metrics = isset($data['additional_metrics']) && is_array($data['additional_metrics'])
            ? AdditionalMetricsData::fromArray($data['additional_metrics'])
            : null;
        $obj->prescription = PrescriptionData::fromArray($data['prescription'] ?? []);

        return $obj;
    }
}
