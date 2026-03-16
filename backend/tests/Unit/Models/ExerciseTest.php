<?php

namespace Tests\Unit\Models;

use App\Models\BlockExercise;
use App\Models\Exercise;
use App\Models\WorkoutBlock;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExerciseTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes(): void
    {
        $exercise = new Exercise();

        $this->assertSame(
            ['name', 'category', 'muscle_group', 'equipment', 'instructions', 'infos', 'additional_metrics'],
            $exercise->getFillable()
        );
    }

    public function test_block_exercises_returns_has_many_relation(): void
    {
        $exercise = new Exercise();

        $this->assertInstanceOf(HasMany::class, $exercise->blockExercises());
    }

    public function test_workout_blocks_returns_belongs_to_many_relation(): void
    {
        $exercise = new Exercise();

        $this->assertInstanceOf(BelongsToMany::class, $exercise->workoutBlocks());
    }

    public function test_exercise_has_many_block_exercises(): void
    {
        // Arrange
        $exercise = Exercise::factory()->create();

        // Act
        BlockExercise::factory()->count(2)->create(['exercise_id' => $exercise->id]);

        // Assert
        $this->assertCount(2, $exercise->blockExercises);
        $this->assertInstanceOf(BlockExercise::class, $exercise->blockExercises->first());
    }

    public function test_exercise_belongs_to_many_workout_blocks(): void
    {
        // Arrange
        $exercise = Exercise::factory()->create();
        $workoutBlocks = WorkoutBlock::factory()->count(2)->create();

        // Act
        $workoutBlocks->each(function (WorkoutBlock $workoutBlock) use ($exercise): void {
            BlockExercise::factory()->create([
                'workout_block_id' => $workoutBlock->id,
                'exercise_id' => $exercise->id,
            ]);
        });

        // Assert
        $this->assertCount(2, $exercise->workoutBlocks);
        $this->assertInstanceOf(WorkoutBlock::class, $exercise->workoutBlocks->first());
    }

    public function test_muscle_group_is_nullable(): void
    {
        // Arrange & Act
        $exercise = Exercise::factory()->create(['muscle_group' => null]);

        // Assert
        $this->assertNull($exercise->muscle_group);
    }

    public function test_equipment_is_nullable(): void
    {
        // Arrange & Act
        $exercise = Exercise::factory()->create(['equipment' => null]);

        // Assert
        $this->assertNull($exercise->equipment);
    }

    public function test_instructions_is_nullable(): void
    {
        // Arrange & Act
        $exercise = Exercise::factory()->create(['instructions' => null]);

        // Assert
        $this->assertNull($exercise->instructions);
    }

    public function test_infos_is_nullable(): void
    {
        // Arrange & Act
        $exercise = Exercise::factory()->create(['infos' => null]);

        // Assert
        $this->assertNull($exercise->infos);
    }

    public function test_additional_metrics_is_nullable(): void
    {
        // Arrange & Act
        $exercise = Exercise::factory()->create(['additional_metrics' => null]);

        // Assert
        $this->assertNull($exercise->additional_metrics);
    }

    public function test_additional_metrics_is_cast_to_array(): void
    {
        // Arrange & Act
        $exercise = Exercise::factory()->create([
            'additional_metrics' => ['calories_burned_per_minute' => 8.5, 'met_value' => 6.0],
        ]);

        // Assert
        $this->assertIsArray($exercise->additional_metrics);
        $this->assertSame(8.5, $exercise->additional_metrics['calories_burned_per_minute']);
    }

    public function test_exercise_can_be_created_with_factory(): void
    {
        $exercise = Exercise::factory()->create();

        $this->assertNotNull($exercise->id);
        $this->assertNotEmpty($exercise->category);
    }
}
