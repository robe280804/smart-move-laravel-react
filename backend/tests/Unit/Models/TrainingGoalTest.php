<?php

namespace Tests\Unit\Models;

use App\Enums\TrainingGoalType;
use App\Models\TrainingGoal;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingGoalTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes(): void
    {
        $trainingGoal = new TrainingGoal();

        $this->assertSame(['user_id', 'goal'], $trainingGoal->getFillable());
    }

    public function test_casts_goal_as_enum(): void
    {
        $trainingGoal = new TrainingGoal();

        $this->assertArrayHasKey('goal', $trainingGoal->getCasts());
        $this->assertSame(TrainingGoalType::class, $trainingGoal->getCasts()['goal']);
    }

    public function test_user_returns_belongs_to_relation(): void
    {
        $trainingGoal = new TrainingGoal();

        $this->assertInstanceOf(BelongsTo::class, $trainingGoal->user());
    }

    public function test_training_goal_belongs_to_user(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $trainingGoal = TrainingGoal::factory()->create(['user_id' => $user->id]);

        // Assert
        $this->assertInstanceOf(User::class, $trainingGoal->user);
        $this->assertSame($user->id, $trainingGoal->user->id);
    }

    public function test_goal_is_cast_to_enum_on_retrieval(): void
    {
        // Arrange
        $trainingGoal = TrainingGoal::factory()->create(['goal' => TrainingGoalType::WeightLoss->value]);

        // Act
        $retrieved = TrainingGoal::find($trainingGoal->id);

        // Assert
        $this->assertInstanceOf(TrainingGoalType::class, $retrieved->goal);
        $this->assertSame(TrainingGoalType::WeightLoss, $retrieved->goal);
    }

    public function test_training_goal_can_be_created_with_factory(): void
    {
        $trainingGoal = TrainingGoal::factory()->create();

        $this->assertNotNull($trainingGoal->id);
        $this->assertNotNull($trainingGoal->user_id);
        $this->assertInstanceOf(TrainingGoalType::class, $trainingGoal->goal);
    }

    public function test_multiple_training_goals_can_belong_to_same_user(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        TrainingGoal::factory()
            ->sequence(
                ['goal' => TrainingGoalType::WeightLoss->value],
                ['goal' => TrainingGoalType::MuscleGain->value],
                ['goal' => TrainingGoalType::Endurance->value],
            )
            ->count(3)
            ->create(['user_id' => $user->id]);

        // Assert
        $this->assertSame(3, TrainingGoal::where('user_id', $user->id)->count());
    }
}
