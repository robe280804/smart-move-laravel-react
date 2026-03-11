<?php

use App\Enums\ExperienceLevel;
use App\Enums\TrainingGoalType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('workout_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Plan infos
            $table->integer('training_days_per_week');
            $table->enum('goal', array_column(TrainingGoalType::cases(), 'value'));
            $table->enum('experience_level', array_column(ExperienceLevel::cases(), 'value'));
            $table->string('workout_type');  // strength | sprint | mobility | conditioning | rest

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_plans');
    }
};
