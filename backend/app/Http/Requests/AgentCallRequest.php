<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\InputSanitizerService;
use Illuminate\Foundation\Http\FormRequest;

class AgentCallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $sanitizer = app(InputSanitizerService::class);

        $fields = ['injuries', 'sports', 'preferred_exercises', 'additional_notes'];
        $merge = [];

        foreach ($fields as $field) {
            if ($this->has($field) && $this->input($field) !== null) {
                $merge[$field] = $sanitizer->sanitize((string) $this->input($field));
            }
        }

        if (! empty($merge)) {
            $this->merge($merge);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'fitness_goals'           => ['required', 'array', 'min:1', 'max:3'],
            'fitness_goals.*'         => ['required', 'string'],
            'training_days_per_week'  => ['required', 'integer', 'min:1', 'max:7'],
            'available_days'          => ['required', 'array', 'min:1'],
            'available_days.*'        => ['required', 'string'],
            'session_duration'        => ['required', 'integer', 'min:15', 'max:180'],
            'injuries'                => ['nullable', 'string', 'max:500'],
            'equipment'               => ['required', 'array', 'min:1'],
            'equipment.*'             => ['required', 'string'],
            'gym_access'              => ['required', 'boolean'],
            'workout_type'            => ['required', 'array', 'min:1', 'max:3'],
            'workout_type.*'          => ['required', 'string'],
            'sports'                  => ['nullable', 'string', 'max:500'],
            'preferred_exercises'     => ['nullable', 'string', 'max:500'],
            'additional_notes'        => ['nullable', 'string', 'max:1000'],
        ];
    }
}
