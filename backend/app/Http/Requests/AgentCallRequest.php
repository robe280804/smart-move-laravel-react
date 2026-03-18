<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TrainingGoalType;
use App\Enums\WorkoutType;
use App\Services\InputSanitizerService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AgentCallRequest extends FormRequest
{
    /** Days of the week accepted by the scheduler. */
    private const ALLOWED_DAYS = [
        'Monday', 'Tuesday', 'Wednesday', 'Thursday',
        'Friday', 'Saturday', 'Sunday',
    ];

    /** Equipment options that match the frontend EQUIPMENT_OPTIONS constant. */
    private const ALLOWED_EQUIPMENT = [
        'Dumbbells',
        'Barbells',
        'Resistance Bands',
        'Pull-up Bar',
        'Bench',
        'Kettlebells',
        'Cable Machine',
        'Cardio Equipment',
        'Bodyweight Only',
        'everything',   // set automatically when gym_access is true
    ];

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
        $goalValues = array_column(TrainingGoalType::cases(), 'value');
        $workoutValues = array_column(WorkoutType::cases(), 'value');

        return [
            'fitness_goals' => ['required', 'array', 'min:1', 'max:3'],
            'fitness_goals.*' => ['required', 'string', Rule::in($goalValues)],

            'training_days_per_week' => ['required', 'integer', 'min:1', 'max:7'],

            'available_days' => ['required', 'array', 'min:1'],
            'available_days.*' => ['required', 'string', Rule::in(self::ALLOWED_DAYS)],

            'session_duration' => ['required', 'integer', 'min:15', 'max:180'],

            'injuries' => ['nullable', 'string', 'max:500'],

            'equipment' => ['required', 'array', 'min:1'],
            'equipment.*' => ['required', 'string', Rule::in(self::ALLOWED_EQUIPMENT)],

            'gym_access' => ['required', 'boolean'],

            'workout_type' => ['required', 'array', 'min:1', 'max:3'],
            'workout_type.*' => ['required', 'string', Rule::in($workoutValues)],

            'sports' => ['nullable', 'string', 'max:500'],
            'preferred_exercises' => ['nullable', 'string', 'max:500'],
            'additional_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
