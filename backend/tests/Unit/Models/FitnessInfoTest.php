<?php

namespace Tests\Unit\Models;

use App\Enums\ExperienceLevel;
use App\Enums\Gender;
use App\Models\FitnessInfo;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FitnessInfoTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes(): void
    {
        $fitnessInfo = new FitnessInfo();

        $this->assertSame(
            ['user_id', 'height', 'weight', 'age', 'gender', 'experience_level'],
            $fitnessInfo->getFillable()
        );
    }

    public function test_casts_height_as_decimal(): void
    {
        $fitnessInfo = new FitnessInfo();

        $this->assertArrayHasKey('height', $fitnessInfo->getCasts());
        $this->assertSame('decimal:2', $fitnessInfo->getCasts()['height']);
    }

    public function test_casts_weight_as_decimal(): void
    {
        $fitnessInfo = new FitnessInfo();

        $this->assertArrayHasKey('weight', $fitnessInfo->getCasts());
        $this->assertSame('decimal:2', $fitnessInfo->getCasts()['weight']);
    }

    public function test_casts_age_as_integer(): void
    {
        $fitnessInfo = new FitnessInfo();

        $this->assertArrayHasKey('age', $fitnessInfo->getCasts());
        $this->assertSame('integer', $fitnessInfo->getCasts()['age']);
    }

    public function test_casts_gender_as_enum(): void
    {
        $fitnessInfo = new FitnessInfo();

        $this->assertArrayHasKey('gender', $fitnessInfo->getCasts());
        $this->assertSame(Gender::class, $fitnessInfo->getCasts()['gender']);
    }

    public function test_casts_experience_level_as_enum(): void
    {
        $fitnessInfo = new FitnessInfo();

        $this->assertArrayHasKey('experience_level', $fitnessInfo->getCasts());
        $this->assertSame(ExperienceLevel::class, $fitnessInfo->getCasts()['experience_level']);
    }

    public function test_user_returns_belongs_to_relation(): void
    {
        $fitnessInfo = new FitnessInfo();

        $this->assertInstanceOf(BelongsTo::class, $fitnessInfo->user());
    }

    public function test_fitness_info_belongs_to_user(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $fitnessInfo = FitnessInfo::factory()->create(['user_id' => $user->id]);

        // Assert
        $this->assertInstanceOf(User::class, $fitnessInfo->user);
        $this->assertSame($user->id, $fitnessInfo->user->id);
    }

    public function test_gender_is_cast_to_enum_on_retrieval(): void
    {
        // Arrange
        $fitnessInfo = FitnessInfo::factory()->create(['gender' => Gender::Male->value]);

        // Act
        $retrieved = FitnessInfo::find($fitnessInfo->id);

        // Assert
        $this->assertInstanceOf(Gender::class, $retrieved->gender);
        $this->assertSame(Gender::Male, $retrieved->gender);
    }

    public function test_experience_level_is_cast_to_enum_on_retrieval(): void
    {
        // Arrange
        $fitnessInfo = FitnessInfo::factory()->create(['experience_level' => ExperienceLevel::Beginner->value]);

        // Act
        $retrieved = FitnessInfo::find($fitnessInfo->id);

        // Assert
        $this->assertInstanceOf(ExperienceLevel::class, $retrieved->experience_level);
        $this->assertSame(ExperienceLevel::Beginner, $retrieved->experience_level);
    }

    public function test_fitness_info_can_be_created_with_factory(): void
    {
        $fitnessInfo = FitnessInfo::factory()->create();

        $this->assertNotNull($fitnessInfo->id);
        $this->assertNotNull($fitnessInfo->user_id);
    }
}
