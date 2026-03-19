<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedbackResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user', fn () => [
                'name' => $this->user->name,
                'surname' => $this->user->surname,
                'email' => $this->user->email,
            ]),
            'rating' => $this->rating,
            'message' => $this->message,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
