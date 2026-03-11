<?php

namespace Tests\Unit\Models;

use App\Models\PlanDay;
use App\Models\WorkoutBlock;
use App\Models\WorkoutPlan;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanDayTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes(): void
    {
        $planDay = new PlanDay();

        $this->assertSame(
            ['workout_plan_id', 'day_of_week', 'workout_name', 'duration_minutes'],
            $planDay->getFillable()
        );
    }

    public function test_casts_day_of_week_as_integer(): void
    {
        $planDay = new PlanDay();

        $this->assertArrayHasKey('day_of_week', $planDay->getCasts());
        $this->assertSame('integer', $planDay->getCasts()['day_of_week']);
    }

    public function test_casts_duration_minutes_as_integer(): void
    {
        $planDay = new PlanDay();

        $this->assertArrayHasKey('duration_minutes', $planDay->getCasts());
        $this->assertSame('integer', $planDay->getCasts()['duration_minutes']);
    }

    public function test_workout_plan_returns_belongs_to_relation(): void
    {
        $planDay = new PlanDay();

        $this->assertInstanceOf(BelongsTo::class, $planDay->workoutPlan());
    }

    public function test_workout_blocks_returns_has_many_relation(): void
    {
        $planDay = new PlanDay();

        $this->assertInstanceOf(HasMany::class, $planDay->workoutBlocks());
    }

    public function test_plan_day_belongs_to_workout_plan(): void
    {
        // Arrange
        $workoutPlan = WorkoutPlan::factory()->create();

        // Act
        $planDay = PlanDay::factory()->create(['workout_plan_id' => $workoutPlan->id]);

        // Assert
        $this->assertInstanceOf(WorkoutPlan::class, $planDay->workoutPlan);
        $this->assertSame($workoutPlan->id, $planDay->workoutPlan->id);
    }

    public function test_plan_day_has_many_workout_blocks(): void
    {
        // Arrange
        $planDay = PlanDay::factory()->create();

        // Act
        WorkoutBlock::factory()->count(4)->create(['plan_day_id' => $planDay->id]);

        // Assert
        $this->assertCount(4, $planDay->workoutBlocks);
        $this->assertInstanceOf(WorkoutBlock::class, $planDay->workoutBlocks->first());
    }

    public function test_workout_name_is_nullable(): void
    {
        // Arrange & Act
        $planDay = PlanDay::factory()->create(['workout_name' => null]);

        // Assert
        $this->assertNull($planDay->workout_name);
    }

    public function test_plan_day_can_be_created_with_factory(): void
    {
        $planDay = PlanDay::factory()->create();

        $this->assertNotNull($planDay->id);
        $this->assertNotNull($planDay->workout_plan_id);
        $this->assertIsInt($planDay->day_of_week);
        $this->assertIsInt($planDay->duration_minutes);
    }
}
