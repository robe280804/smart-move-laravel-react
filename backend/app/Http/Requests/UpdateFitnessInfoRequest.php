<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ExperienceLevel;
use App\Enums\Gender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFitnessInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'height' => ['sometimes', 'numeric', 'min:50', 'max:300'],
            'weight' => ['sometimes', 'numeric', 'min:10', 'max:500'],
            'age' => ['sometimes', 'integer', 'min:10', 'max:120'],
            'gender' => ['sometimes', 'string', Rule::enum(Gender::class)],
            'experience_level' => ['sometimes', 'string', Rule::enum(ExperienceLevel::class)],
        ];
    }
}
