<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email,
            'role' => $this->getRoleNames()->first(),
            'email_verified' => $this->email_verified_at !== null,
            'plan' => $this->whenLoaded('subscriptions', function (): string {
                $active = $this->subscriptions
                    ->whereIn('stripe_status', ['active', 'trialing'])
                    ->first();

                if ($active === null) {
                    return 'free';
                }

                return match ($active->stripe_price) {
                    config('plans.stripe_prices.advanced') => 'advanced',
                    config('plans.stripe_prices.pro') => 'pro',
                    default => 'free',
                };
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
