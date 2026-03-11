<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgentResumeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'resume_token'          => ['required', 'string'],
            'actions'               => ['required', 'array', 'min:1'],
            'actions.*.id'          => ['required', 'string'],
            'actions.*.decision'    => ['required', 'string', 'in:approved,rejected,edit,pending'],
            'actions.*.feedback'    => ['nullable', 'string', 'max:1000'],
        ];
    }
}
