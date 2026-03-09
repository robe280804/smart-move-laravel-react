<?php

namespace Tests\Unit\Models;

use App\Models\FitnessInfo;
use App\Models\TrainingGoal;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes(): void
    {
        $user = new User();

        $this->assertSame(['name', 'surname', 'email', 'password'], $user->getFillable());
    }

    public function test_hidden_attributes(): void
    {
        $user = new User();

        $this->assertContains('password', $user->getHidden());
        $this->assertContains('remember_token', $user->getHidden());
    }

    public function test_casts_email_verified_at_as_datetime(): void
    {
        $user = new User();

        $this->assertArrayHasKey('email_verified_at', $user->getCasts());
        $this->assertSame('datetime', $user->getCasts()['email_verified_at']);
    }

    public function test_casts_password_as_hashed(): void
    {
        $user = new User();

        $this->assertArrayHasKey('password', $user->getCasts());
        $this->assertSame('hashed', $user->getCasts()['password']);
    }

    public function test_fitness_info_returns_has_one_relation(): void
    {
        $user = new User();

        $this->assertInstanceOf(HasOne::class, $user->fitnessInfo());
    }

    public function test_training_goals_returns_has_many_relation(): void
    {
        $user = new User();

        $this->assertInstanceOf(HasMany::class, $user->trainingGoals());
    }

    public function test_user_has_one_fitness_info(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        FitnessInfo::factory()->create(['user_id' => $user->id]);

        // Assert
        $this->assertInstanceOf(FitnessInfo::class, $user->fitnessInfo);
    }

    public function test_user_has_many_training_goals(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        TrainingGoal::factory()->count(3)->create(['user_id' => $user->id]);

        // Assert
        $this->assertCount(3, $user->trainingGoals);
        $this->assertInstanceOf(TrainingGoal::class, $user->trainingGoals->first());
    }

    public function test_user_can_be_created_with_factory(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->id);
        $this->assertNotEmpty($user->name);
        $this->assertNotEmpty($user->email);
    }

    public function test_unverified_factory_state_sets_null_email_verified_at(): void
    {
        $user = User::factory()->unverified()->create();

        $this->assertNull($user->email_verified_at);
    }
}
