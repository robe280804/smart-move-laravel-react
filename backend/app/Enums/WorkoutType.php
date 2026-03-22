<?php

namespace App\Enums;

enum WorkoutType: string
{
    case Strength = 'strength';
    case Cardio = 'cardio';
    case Mobility = 'mobility';
    case Conditioning = 'conditioning';
    case Hiit = 'hiit';
    case Bodyweight = 'bodyweight';
    case Functional = 'functional';
    case Core = 'core';
    case Recovery = 'recovery';

    public function label(): string
    {
        return match ($this) {
            self::Strength => 'Strength Training',
            self::Cardio => 'Cardio',
            self::Mobility => 'Mobility & Flexibility',
            self::Conditioning => 'Conditioning',
            self::Hiit => 'HIIT',
            self::Bodyweight => 'Bodyweight / Calisthenics',
            self::Functional => 'Functional Training',
            self::Core => 'Core Training',
            self::Recovery => 'Recovery / Stretching',
        };
    }
}
