<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBlockExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'sets' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:100'],
            'reps' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:100'],
            'weight' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:800'],
            'duration_seconds' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:3600'],
            'rest_seconds' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:600'],
            'rpe' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:10'],
        ];
    }
}
