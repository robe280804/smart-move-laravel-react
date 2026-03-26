<?php

namespace Tests\Unit\Models;

use App\Enums\ExperienceLevel;
use App\Enums\TrainingGoalType;
use App\Models\PlanDay;
use App\Models\User;
use App\Models\WorkoutPlan;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkoutPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes(): void
    {
        $workoutPlan = new WorkoutPlan();

        $this->assertSame(
            ['user_id', 'status', 'training_days_per_week', 'goal', 'experience_level', 'workout_type', 'generation_request', 'failure_reason'],
            $workoutPlan->getFillable()
        );
    }

    public function test_casts_training_days_per_week_as_integer(): void
    {
        $workoutPlan = new WorkoutPlan();

        $this->assertArrayHasKey('training_days_per_week', $workoutPlan->getCasts());
        $this->assertSame('integer', $workoutPlan->getCasts()['training_days_per_week']);
    }

    public function test_casts_goal_as_enum(): void
    {
        $workoutPlan = new WorkoutPlan();

        $this->assertArrayHasKey('goal', $workoutPlan->getCasts());
        $this->assertSame(TrainingGoalType::class, $workoutPlan->getCasts()['goal']);
    }

    public function test_casts_experience_level_as_enum(): void
    {
        $workoutPlan = new WorkoutPlan();

        $this->assertArrayHasKey('experience_level', $workoutPlan->getCasts());
        $this->assertSame(ExperienceLevel::class, $workoutPlan->getCasts()['experience_level']);
    }

    public function test_user_returns_belongs_to_relation(): void
    {
        $workoutPlan = new WorkoutPlan();

        $this->assertInstanceOf(BelongsTo::class, $workoutPlan->user());
    }

    public function test_plan_days_returns_has_many_relation(): void
    {
        $workoutPlan = new WorkoutPlan();

        $this->assertInstanceOf(HasMany::class, $workoutPlan->planDays());
    }

    public function test_workout_plan_belongs_to_user(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $workoutPlan = WorkoutPlan::factory()->create(['user_id' => $user->id]);

        // Assert
        $this->assertInstanceOf(User::class, $workoutPlan->user);
        $this->assertSame($user->id, $workoutPlan->user->id);
    }

    public function test_workout_plan_has_many_plan_days(): void
    {
        // Arrange
        $workoutPlan = WorkoutPlan::factory()->create();

        // Act
        PlanDay::factory()->count(3)->create(['workout_plan_id' => $workoutPlan->id]);

        // Assert
        $this->assertCount(3, $workoutPlan->planDays);
        $this->assertInstanceOf(PlanDay::class, $workoutPlan->planDays->first());
    }

    public function test_goal_is_cast_to_enum_on_retrieval(): void
    {
        // Arrange
        $workoutPlan = WorkoutPlan::factory()->create(['goal' => TrainingGoalType::MuscleGain->value]);

        // Act
        $retrieved = WorkoutPlan::find($workoutPlan->id);

        // Assert
        $this->assertInstanceOf(TrainingGoalType::class, $retrieved->goal);
        $this->assertSame(TrainingGoalType::MuscleGain, $retrieved->goal);
    }

    public function test_experience_level_is_cast_to_enum_on_retrieval(): void
    {
        // Arrange
        $workoutPlan = WorkoutPlan::factory()->create(['experience_level' => ExperienceLevel::Intermediate->value]);

        // Act
        $retrieved = WorkoutPlan::find($workoutPlan->id);

        // Assert
        $this->assertInstanceOf(ExperienceLevel::class, $retrieved->experience_level);
        $this->assertSame(ExperienceLevel::Intermediate, $retrieved->experience_level);
    }

    public function test_workout_plan_can_be_created_with_factory(): void
    {
        $workoutPlan = WorkoutPlan::factory()->create();

        $this->assertNotNull($workoutPlan->id);
        $this->assertNotNull($workoutPlan->user_id);
        $this->assertInstanceOf(TrainingGoalType::class, $workoutPlan->goal);
        $this->assertInstanceOf(ExperienceLevel::class, $workoutPlan->experience_level);
    }

    public function test_multiple_workout_plans_can_belong_to_same_user(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        WorkoutPlan::factory()->count(3)->create(['user_id' => $user->id]);

        // Assert
        $this->assertSame(3, WorkoutPlan::where('user_id', $user->id)->count());
    }
}
