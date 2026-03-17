<?php

namespace App\Enums;

enum WorkoutPlanStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
}
