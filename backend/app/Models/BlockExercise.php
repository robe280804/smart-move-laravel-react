<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockExercise extends Model
{
    /** @use HasFactory<\Database\Factories\BlockExerciseFactory> */
    use HasFactory;

    protected $fillable = [
        'workout_block_id',
        'exercise_id',
        'order',
        'sets',
        'reps',
        'weight',
        'duration_seconds',
        'rest_seconds',
        'rpe',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'sets' => 'integer',
            'reps' => 'integer',
            'weight' => 'decimal:2',
            'duration_seconds' => 'integer',
            'rest_seconds' => 'integer',
            'rpe' => 'decimal:1',
        ];
    }

    public function workoutBlock(): BelongsTo
    {
        return $this->belongsTo(WorkoutBlock::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }
}
