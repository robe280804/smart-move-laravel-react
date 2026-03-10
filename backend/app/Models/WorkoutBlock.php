<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkoutBlock extends Model
{
    /** @use HasFactory<\Database\Factories\WorkoutBlockFactory> */
    use HasFactory;

    protected $fillable = [
        'plan_day_id',
        'name',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function planDay(): BelongsTo
    {
        return $this->belongsTo(PlanDay::class);
    }

    public function blockExercises(): HasMany
    {
        return $this->hasMany(BlockExercise::class);
    }

    public function exercises(): BelongsToMany
    {
        return $this->belongsToMany(Exercise::class, 'block_exercises')
            ->withPivot(['order', 'sets', 'reps', 'weight', 'duration_seconds', 'rest_seconds', 'rpe', 'info', 'additional_metrics'])
            ->withTimestamps();
    }
}
