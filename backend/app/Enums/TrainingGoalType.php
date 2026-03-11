<?php

namespace App\Enums;

enum TrainingGoalType: string
{
    case WeightLoss = 'weight_loss';
    case MuscleGain = 'muscle_gain';
    case Endurance = 'endurance';
    case Flexibility = 'flexibility';
    case StrengthBuilding = 'strength_building';
    case GeneralFitness = 'general_fitness';
}
