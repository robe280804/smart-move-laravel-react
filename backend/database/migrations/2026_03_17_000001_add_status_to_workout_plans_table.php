<?php

use App\Enums\ExperienceLevel;
use App\Enums\TrainingGoalType;
use App\Enums\WorkoutPlanStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workout_plans', function (Blueprint $table) {
            $table->enum('status', array_column(WorkoutPlanStatus::cases(), 'value'))
                ->default(WorkoutPlanStatus::Pending->value)
                ->after('user_id');

            $table->integer('training_days_per_week')->nullable()->change();
            $table->enum('goal', array_column(TrainingGoalType::cases(), 'value'))->nullable()->change();
            $table->enum('experience_level', array_column(ExperienceLevel::cases(), 'value'))->nullable()->change();
            $table->string('workout_type')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('workout_plans', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->integer('training_days_per_week')->nullable(false)->change();
            $table->enum('goal', array_column(TrainingGoalType::cases(), 'value'))->nullable(false)->change();
            $table->enum('experience_level', array_column(ExperienceLevel::cases(), 'value'))->nullable(false)->change();
            $table->string('workout_type')->nullable(false)->change();
        });
    }
};
