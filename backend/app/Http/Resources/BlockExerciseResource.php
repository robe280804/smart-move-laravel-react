<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlockExerciseResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'workout_block_id' => $this->workout_block_id,
            'exercise_id'      => $this->exercise_id,
            'order'            => $this->order,
            'sets'             => $this->sets,
            'reps'             => $this->reps,
            'weight'           => $this->weight,
            'duration_seconds' => $this->duration_seconds,
            'rest_seconds'     => $this->rest_seconds,
            'rpe'              => $this->rpe,
            'exercise'         => new ExerciseResource($this->whenLoaded('exercise')),
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
        ];
    }
}
