<?php

declare(strict_types=1);

namespace App\Ai\Agents\StructuredOutput;

class AdditionalMetricsData
{
    public ?string $description;

    public ?float $met_value;

    public ?string $energy_system;

    public ?string $difficulty;

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $obj = new self;
        $obj->description = $data['description'] ?? null;
        $obj->met_value = isset($data['met_value']) ? (float) $data['met_value'] : null;
        $obj->energy_system = $data['energy_system'] ?? null;
        $obj->difficulty = $data['difficulty'] ?? null;

        return $obj;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'description' => $this->description,
            'met_value' => $this->met_value,
            'energy_system' => $this->energy_system,
            'difficulty' => $this->difficulty,
        ];
    }
}
