<?php

namespace App\Enums;

enum TrainingGoalType: string
{
    case WeightLoss = 'weight_loss';
    case MuscleGain = 'muscle_gain';
    case StrengthBuilding = 'strength_building';
    case Endurance = 'endurance';
    case Flexibility = 'flexibility';
    case GeneralFitness = 'general_fitness';
    case BodyRecomposition = 'body_recomposition';
    case AthleticPerformance = 'athletic_performance';
    case Rehabilitation = 'rehabilitation';
    case PostureCorrection = 'posture_correction';
    case FunctionalFitness = 'functional_fitness';

    public function label(): string
    {
        return match ($this) {
            self::WeightLoss => 'Weight Loss',
            self::MuscleGain => 'Muscle Gain',
            self::StrengthBuilding => 'Strength Building',
            self::Endurance => 'Endurance',
            self::Flexibility => 'Flexibility',
            self::GeneralFitness => 'General Fitness',
            self::BodyRecomposition => 'Body Recomposition',
            self::AthleticPerformance => 'Athletic Performance',
            self::Rehabilitation => 'Injury Recovery',
            self::PostureCorrection => 'Posture Correction',
            self::FunctionalFitness => 'Functional Fitness',
        };
    }
}
