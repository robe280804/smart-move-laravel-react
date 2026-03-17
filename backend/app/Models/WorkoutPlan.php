<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ExperienceLevel;
use App\Enums\TrainingGoalType;
use App\Enums\WorkoutPlanStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkoutPlan extends Model
{
    /** @use HasFactory<\Database\Factories\WorkoutPlanFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'training_days_per_week',
        'goal',
        'experience_level',
        'workout_type',
    ];

    protected function casts(): array
    {
        return [
            'status' => WorkoutPlanStatus::class,
            'training_days_per_week' => 'integer',
            'goal' => TrainingGoalType::class,
            'experience_level' => ExperienceLevel::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function planDays(): HasMany
    {
        return $this->hasMany(PlanDay::class);
    }
}
