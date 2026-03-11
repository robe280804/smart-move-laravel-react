<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanDay extends Model
{
    /** @use HasFactory<\Database\Factories\PlanDayFactory> */
    use HasFactory;

    protected $fillable = [
        'workout_plan_id',
        'day_of_week',
        'workout_name',
        'duration_minutes',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'duration_minutes' => 'integer',
        ];
    }

    public function workoutPlan(): BelongsTo
    {
        return $this->belongsTo(WorkoutPlan::class);
    }

    public function workoutBlocks(): HasMany
    {
        return $this->hasMany(WorkoutBlock::class);
    }
}
