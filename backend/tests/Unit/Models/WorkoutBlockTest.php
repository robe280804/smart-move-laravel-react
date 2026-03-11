<?php

namespace Tests\Unit\Models;

use App\Models\BlockExercise;
use App\Models\Exercise;
use App\Models\PlanDay;
use App\Models\WorkoutBlock;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkoutBlockTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes(): void
    {
        $workoutBlock = new WorkoutBlock();

        $this->assertSame(
            ['plan_day_id', 'name', 'order'],
            $workoutBlock->getFillable()
        );
    }

    public function test_casts_order_as_integer(): void
    {
        $workoutBlock = new WorkoutBlock();

        $this->assertArrayHasKey('order', $workoutBlock->getCasts());
        $this->assertSame('integer', $workoutBlock->getCasts()['order']);
    }

    public function test_plan_day_returns_belongs_to_relation(): void
    {
        $workoutBlock = new WorkoutBlock();

        $this->assertInstanceOf(BelongsTo::class, $workoutBlock->planDay());
    }

    public function test_block_exercises_returns_has_many_relation(): void
    {
        $workoutBlock = new WorkoutBlock();

        $this->assertInstanceOf(HasMany::class, $workoutBlock->blockExercises());
    }

    public function test_exercises_returns_belongs_to_many_relation(): void
    {
        $workoutBlock = new WorkoutBlock();

        $this->assertInstanceOf(BelongsToMany::class, $workoutBlock->exercises());
    }

    public function test_workout_block_belongs_to_plan_day(): void
    {
        // Arrange
        $planDay = PlanDay::factory()->create();

        // Act
        $workoutBlock = WorkoutBlock::factory()->create(['plan_day_id' => $planDay->id]);

        // Assert
        $this->assertInstanceOf(PlanDay::class, $workoutBlock->planDay);
        $this->assertSame($planDay->id, $workoutBlock->planDay->id);
    }

    public function test_workout_block_has_many_block_exercises(): void
    {
        // Arrange
        $workoutBlock = WorkoutBlock::factory()->create();

        // Act
        BlockExercise::factory()->count(3)->create(['workout_block_id' => $workoutBlock->id]);

        // Assert
        $this->assertCount(3, $workoutBlock->blockExercises);
        $this->assertInstanceOf(BlockExercise::class, $workoutBlock->blockExercises->first());
    }

    public function test_workout_block_belongs_to_many_exercises(): void
    {
        // Arrange
        $workoutBlock = WorkoutBlock::factory()->create();
        $exercises = Exercise::factory()->count(3)->create();

        // Act
        $exercises->each(function (Exercise $exercise) use ($workoutBlock): void {
            BlockExercise::factory()->create([
                'workout_block_id' => $workoutBlock->id,
                'exercise_id' => $exercise->id,
            ]);
        });

        // Assert
        $this->assertCount(3, $workoutBlock->exercises);
        $this->assertInstanceOf(Exercise::class, $workoutBlock->exercises->first());
    }

    public function test_workout_block_can_be_created_with_factory(): void
    {
        $workoutBlock = WorkoutBlock::factory()->create();

        $this->assertNotNull($workoutBlock->id);
        $this->assertNotNull($workoutBlock->plan_day_id);
        $this->assertNotEmpty($workoutBlock->name);
        $this->assertIsInt($workoutBlock->order);
    }
}
