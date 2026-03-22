<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkoutPlanResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'training_days_per_week' => $this->training_days_per_week,
            'goal' => $this->goal,
            'experience_level' => $this->experience_level,
            'workout_type' => $this->workout_type,
            'generation_request' => $this->generation_request,
            'plan_days' => PlanDayResource::collection($this->whenLoaded('planDays')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
