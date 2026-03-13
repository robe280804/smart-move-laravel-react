<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgentCallRequest extends FormRequest
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
            'message'                              => ['nullable', 'string', 'max:2000'],
            'fitness_data'                         => ['required', 'array'],
            'fitness_data.fitness_goal'            => ['required', 'string', 'in:weight_loss,muscle_gain,endurance,flexibility,strength_building,general_fitness'],
            'fitness_data.training_days_per_week'  => ['required', 'integer', 'min:1', 'max:7'],
            'fitness_data.available_days'          => ['required', 'array', 'min:1'],
            'fitness_data.available_days.*'        => ['required', 'string', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'fitness_data.session_duration'        => ['required', 'integer', 'min:15', 'max:240'],
            'fitness_data.rest_days'               => ['nullable', 'integer', 'min:0', 'max:7'],
            'fitness_data.injuries'                => ['nullable', 'string', 'max:500'],
            'fitness_data.equipment'               => ['nullable', 'string', 'max:500'],
            'fitness_data.preferred_workout_type'  => ['nullable', 'string', 'in:strength,cardio,mobility,conditioning'],
        ];
    }
}
