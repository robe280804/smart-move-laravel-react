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
}
