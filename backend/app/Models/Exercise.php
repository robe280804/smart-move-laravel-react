<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exercise extends Model
{
    /** @use HasFactory<\Database\Factories\ExerciseFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'muscle_group',
        'equipment',
        'instructions',
        'infos',
        'additional_metrics',
    ];

    protected function casts(): array
    {
        return [
            'additional_metrics' => 'array',
        ];
    }

    public function blockExercises(): HasMany
    {
        return $this->hasMany(BlockExercise::class);
    }

    public function workoutBlocks(): BelongsToMany
    {
        return $this->belongsToMany(WorkoutBlock::class, 'block_exercises')
            ->withPivot(['order', 'sets', 'reps', 'weight', 'duration_seconds', 'rest_seconds', 'rpe'])
            ->withTimestamps();
    }
}
