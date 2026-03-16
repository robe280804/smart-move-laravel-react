<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExerciseResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'category'           => $this->category,
            'muscle_group'       => $this->muscle_group,
            'equipment'          => $this->equipment,
            'instructions'       => $this->instructions,
            'infos'              => $this->infos,
            'additional_metrics' => $this->additional_metrics,
            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,
        ];
    }
}
