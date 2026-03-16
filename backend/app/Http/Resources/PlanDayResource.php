<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanDayResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'workout_plan_id'  => $this->workout_plan_id,
            'day_of_week'      => $this->day_of_week,
            'workout_name'     => $this->workout_name,
            'duration_minutes' => $this->duration_minutes,
            'workout_blocks'   => WorkoutBlockResource::collection($this->whenLoaded('workoutBlocks')),
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
        ];
    }
}
