<?php

namespace App\Models;

use App\Enums\ExperienceLevel;
use App\Enums\Gender;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FitnessInfo extends Model
{
    /** @use HasFactory<\Database\Factories\FitnessInfoFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'height',
        'weight',
        'age',
        'gender',
        'experience_level',
    ];

    protected function casts(): array
    {
        return [
            'height' => 'decimal:2',
            'weight' => 'decimal:2',
            'age' => 'integer',
            'gender' => Gender::class,
            'experience_level' => ExperienceLevel::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
