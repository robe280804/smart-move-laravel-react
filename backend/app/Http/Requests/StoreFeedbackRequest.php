<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'message' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
