<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ExperienceLevel;
use App\Enums\Gender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFitnessInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'height' => ['required', 'numeric', 'min:50', 'max:300'],
            'weight' => ['required', 'numeric', 'min:10', 'max:500'],
            'age' => ['required', 'integer', 'min:10', 'max:120'],
            'gender' => ['required', 'string', Rule::enum(Gender::class)],
            'experience_level' => ['required', 'string', Rule::enum(ExperienceLevel::class)],
        ];
    }
}
