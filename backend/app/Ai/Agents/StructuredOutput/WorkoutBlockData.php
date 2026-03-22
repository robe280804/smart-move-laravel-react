<?php

declare(strict_types=1);

namespace App\Ai\Agents\StructuredOutput;

class WorkoutBlockData
{
    public string $name;

    public int $order;

    /** @var ExerciseData[] */
    public array $exercises;

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $obj = new self;
        $obj->name = (string) $data['name'];
        $obj->order = (int) $data['order'];
        $obj->exercises = array_map(
            fn (array $e) => ExerciseData::fromArray($e),
            $data['exercises'] ?? [],
        );

        return $obj;
    }
}
