<?php

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
        Schema::create('block_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_block_id')
                ->constrained('workout_blocks')
                ->cascadeOnDelete();

            $table->foreignId('exercise_id')
                ->constrained('exercises');

            $table->integer('order')->nullable();

            $table->integer('sets')->nullable();
            $table->integer('reps')->nullable();
            $table->decimal('weight', 6, 2)->nullable();

            $table->integer('duration_seconds')->nullable();
            $table->integer('rest_seconds')->nullable();

            $table->decimal('rpe', 3, 1)->nullable();  // effort level 8.5 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('block_exercises');
    }
};
