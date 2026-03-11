<?php

namespace App\Models;

use App\Enums\TrainingGoalType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingGoal extends Model
{
    /** @use HasFactory<\Database\Factories\TrainingGoalFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'goal',
    ];

    protected function casts(): array
    {
        return [
            'goal' => TrainingGoalType::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
