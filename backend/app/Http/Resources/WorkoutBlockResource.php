<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkoutBlockResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'plan_day_id'     => $this->plan_day_id,
            'name'            => $this->name,
            'order'           => $this->order,
            'block_exercises' => BlockExerciseResource::collection($this->whenLoaded('blockExercises')),
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}
