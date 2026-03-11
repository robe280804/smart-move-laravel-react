<?php

namespace Tests\Unit\Models;

use App\Models\BlockExercise;
use App\Models\Exercise;
use App\Models\WorkoutBlock;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlockExerciseTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes(): void
    {
        $blockExercise = new BlockExercise();

        $this->assertSame(
            ['workout_block_id', 'exercise_id', 'order', 'sets', 'reps', 'weight', 'duration_seconds', 'rest_seconds', 'rpe'],
            $blockExercise->getFillable()
        );
    }

    public function test_casts_order_as_integer(): void
    {
        $blockExercise = new BlockExercise();

        $this->assertArrayHasKey('order', $blockExercise->getCasts());
        $this->assertSame('integer', $blockExercise->getCasts()['order']);
    }

    public function test_casts_sets_as_integer(): void
    {
        $blockExercise = new BlockExercise();

        $this->assertArrayHasKey('sets', $blockExercise->getCasts());
        $this->assertSame('integer', $blockExercise->getCasts()['sets']);
    }

    public function test_casts_reps_as_integer(): void
    {
        $blockExercise = new BlockExercise();

        $this->assertArrayHasKey('reps', $blockExercise->getCasts());
        $this->assertSame('integer', $blockExercise->getCasts()['reps']);
    }

    public function test_casts_weight_as_decimal(): void
    {
        $blockExercise = new BlockExercise();

        $this->assertArrayHasKey('weight', $blockExercise->getCasts());
        $this->assertSame('decimal:2', $blockExercise->getCasts()['weight']);
    }

    public function test_casts_duration_seconds_as_integer(): void
    {
        $blockExercise = new BlockExercise();

        $this->assertArrayHasKey('duration_seconds', $blockExercise->getCasts());
        $this->assertSame('integer', $blockExercise->getCasts()['duration_seconds']);
    }

    public function test_casts_rest_seconds_as_integer(): void
    {
        $blockExercise = new BlockExercise();

        $this->assertArrayHasKey('rest_seconds', $blockExercise->getCasts());
        $this->assertSame('integer', $blockExercise->getCasts()['rest_seconds']);
    }

    public function test_casts_rpe_as_decimal(): void
    {
        $blockExercise = new BlockExercise();

        $this->assertArrayHasKey('rpe', $blockExercise->getCasts());
        $this->assertSame('decimal:1', $blockExercise->getCasts()['rpe']);
    }

    public function test_workout_block_returns_belongs_to_relation(): void
    {
        $blockExercise = new BlockExercise();

        $this->assertInstanceOf(BelongsTo::class, $blockExercise->workoutBlock());
    }

    public function test_exercise_returns_belongs_to_relation(): void
    {
        $blockExercise = new BlockExercise();

        $this->assertInstanceOf(BelongsTo::class, $blockExercise->exercise());
    }

    public function test_block_exercise_belongs_to_workout_block(): void
    {
        // Arrange
        $workoutBlock = WorkoutBlock::factory()->create();

        // Act
        $blockExercise = BlockExercise::factory()->create(['workout_block_id' => $workoutBlock->id]);

        // Assert
        $this->assertInstanceOf(WorkoutBlock::class, $blockExercise->workoutBlock);
        $this->assertSame($workoutBlock->id, $blockExercise->workoutBlock->id);
    }

    public function test_block_exercise_belongs_to_exercise(): void
    {
        // Arrange
        $exercise = Exercise::factory()->create();

        // Act
        $blockExercise = BlockExercise::factory()->create(['exercise_id' => $exercise->id]);

        // Assert
        $this->assertInstanceOf(Exercise::class, $blockExercise->exercise);
        $this->assertSame($exercise->id, $blockExercise->exercise->id);
    }

    public function test_all_performance_fields_are_nullable(): void
    {
        // Arrange & Act
        $blockExercise = BlockExercise::factory()->create([
            'order' => null,
            'sets' => null,
            'reps' => null,
            'weight' => null,
            'duration_seconds' => null,
            'rest_seconds' => null,
            'rpe' => null,
        ]);

        // Assert
        $this->assertNull($blockExercise->order);
        $this->assertNull($blockExercise->sets);
        $this->assertNull($blockExercise->reps);
        $this->assertNull($blockExercise->weight);
        $this->assertNull($blockExercise->duration_seconds);
        $this->assertNull($blockExercise->rest_seconds);
        $this->assertNull($blockExercise->rpe);
    }

    public function test_block_exercise_can_be_created_with_factory(): void
    {
        $blockExercise = BlockExercise::factory()->create();

        $this->assertNotNull($blockExercise->id);
        $this->assertNotNull($blockExercise->workout_block_id);
        $this->assertNotNull($blockExercise->exercise_id);
    }
}
