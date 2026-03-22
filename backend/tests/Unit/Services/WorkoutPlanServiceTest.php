<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\WorkoutPlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class WorkoutPlanServiceTest extends TestCase
{
    use RefreshDatabase;

    private WorkoutPlanService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(WorkoutPlanService::class);
    }

    // ==================== HAPPY PATH ====================

    public function test_creates_workout_plan_with_correct_top_level_fields(): void
    {
        $user = User::factory()->create();

        $pending = $this->service->createPending($user);
        $plan = $this->service->fillFromAgentResponse($pending, $this->validJson());

        $this->assertDatabaseHas('workout_plans', [
            'user_id' => $user->id,
            'training_days_per_week' => 3,
            'goal' => 'muscle_gain',
            'experience_level' => 'advanced',
            'workout_type' => 'strength',
        ]);

        $this->assertSame($user->id, $plan->user_id);
        $this->assertSame(3, $plan->training_days_per_week);
        $this->assertSame('muscle_gain', $plan->goal->value);
    }

    public function test_creates_all_plan_days(): void
    {
        $user = User::factory()->create();

        $pending = $this->service->createPending($user);
        $plan = $this->service->fillFromAgentResponse($pending, $this->validJsonWithTwoDays());

        $this->assertSame(2, $plan->planDays->count());

        $this->assertDatabaseHas('plan_days', ['day_of_week' => 1, 'workout_name' => 'Upper Body Strength']);
        $this->assertDatabaseHas('plan_days', ['day_of_week' => 3, 'workout_name' => 'Lower Body Strength']);
    }

    public function test_creates_workout_blocks_within_plan_day(): void
    {
        $user = User::factory()->create();

        $pending = $this->service->createPending($user);
        $plan = $this->service->fillFromAgentResponse($pending, $this->validJson());

        $blocks = $plan->planDays->first()->workoutBlocks;

        $this->assertSame(3, $blocks->count());
        $this->assertDatabaseHas('workout_blocks', ['name' => 'Warmup', 'order' => 1]);
        $this->assertDatabaseHas('workout_blocks', ['name' => 'Main Block', 'order' => 2]);
        $this->assertDatabaseHas('workout_blocks', ['name' => 'Cool-Down', 'order' => 3]);
    }

    public function test_creates_exercises_with_all_fields(): void
    {
        $user = User::factory()->create();

        $pending = $this->service->createPending($user);
        $this->service->fillFromAgentResponse($pending, $this->validJson());

        $this->assertDatabaseHas('exercises', [
            'category' => 'Strength',
            'muscle_group' => 'Chest',
            'equipment' => 'Barbell',
        ]);
    }

    public function test_persists_all_prescription_fields_on_block_exercise(): void
    {
        $user = User::factory()->create();

        $pending = $this->service->createPending($user);
        $this->service->fillFromAgentResponse($pending, $this->validJson());

        $this->assertDatabaseHas('block_exercises', [
            'order' => 1,
            'sets' => 4,
            'reps' => 8,
            'weight' => 80.0,
            'duration_seconds' => null,
            'rest_seconds' => 120,
            'rpe' => 8.0,
        ]);
    }

    public function test_loads_relations_on_returned_plan(): void
    {
        $user = User::factory()->create();

        $pending = $this->service->createPending($user);
        $plan = $this->service->fillFromAgentResponse($pending, $this->validJson());

        $this->assertTrue($plan->relationLoaded('planDays'));
        $this->assertTrue($plan->planDays->first()->relationLoaded('workoutBlocks'));
        $this->assertTrue($plan->planDays->first()->workoutBlocks->first()->relationLoaded('blockExercises'));
    }

    // ==================== PARSING ====================

    public function test_strips_markdown_json_fences_from_response(): void
    {
        $user = User::factory()->create();

        $wrappedJson = "```json\n".$this->validJson()."\n```";

        $pending = $this->service->createPending($user);
        $plan = $this->service->fillFromAgentResponse($pending, $wrappedJson);

        $this->assertDatabaseHas('workout_plans', ['user_id' => $user->id]);
        $this->assertSame('muscle_gain', $plan->goal->value);
    }

    public function test_strips_plain_markdown_fences_from_response(): void
    {
        $user = User::factory()->create();

        $wrappedJson = "```\n".$this->validJson()."\n```";

        $pending = $this->service->createPending($user);
        $this->service->fillFromAgentResponse($pending, $wrappedJson);

        $this->assertDatabaseHas('workout_plans', ['user_id' => $user->id]);
    }

    // ==================== FAILURE PATHS ====================

    public function test_throws_exception_for_invalid_json(): void
    {
        $user = User::factory()->create();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Agent response is not valid JSON.');

        $pending = $this->service->createPending($user);
        $this->service->fillFromAgentResponse($pending, 'this is not json');
    }

    public function test_throws_exception_for_truncated_json(): void
    {
        $user = User::factory()->create();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Agent response is not valid JSON.');

        $pending = $this->service->createPending($user);
        $this->service->fillFromAgentResponse($pending, '{"workout_plan":{"training_days_per_week":3,"goal":"muscle_gain"');
    }

    public function test_throws_exception_when_workout_plan_key_is_missing(): void
    {
        $user = User::factory()->create();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Agent response is missing the "workout_plan" key.');

        $pending = $this->service->createPending($user);
        $this->service->fillFromAgentResponse($pending, '{"data":{"foo":"bar"}}');
    }

    public function test_rolls_back_transaction_on_db_failure(): void
    {
        $user = User::factory()->create();

        // Invalid goal value triggers ValueError from TrainingGoalType::from(),
        // causing the transaction to roll back.
        $invalidJson = (string) json_encode([
            'workout_plan' => [
                'training_days_per_week' => 3,
                'goal' => 'invalid_goal',
                'experience_level' => 'advanced',
                'workout_type' => 'strength',
                'plan_days' => [],
            ],
        ]);

        $pending = $this->service->createPending($user);

        try {
            $this->service->fillFromAgentResponse($pending, $invalidJson);
        } catch (\Throwable) {
        }

        $this->assertDatabaseMissing('plan_days', ['workout_plan_id' => $pending->id]);
        $this->assertDatabaseHas('workout_plans', ['id' => $pending->id, 'status' => 'pending']);
    }

    // ==================== HELPERS ====================

    private function validJson(): string
    {
        return (string) json_encode([
            'workout_plan' => [
                'training_days_per_week' => 3,
                'goal' => 'muscle_gain',
                'experience_level' => 'advanced',
                'workout_type' => 'strength',
                'plan_days' => [
                    [
                        'day_of_week' => 1,
                        'workout_name' => 'Upper Body Strength',
                        'duration_minutes' => 60,
                        'workout_blocks' => [
                            [
                                'name' => 'Warmup',
                                'order' => 1,
                                'exercises' => [
                                    [
                                        'name' => 'Light Jog',
                                        'category' => 'Cardio',
                                        'muscle_group' => 'Full Body',
                                        'equipment' => 'Body Only',
                                        'instructions' => 'Light jog for 5 minutes.',
                                        'infos' => 'Raises heart rate.',
                                        'additional_metrics' => ['met_value' => 4.0, 'energy_system' => 'Aerobic', 'difficulty' => 'beginner'],
                                        'prescription' => ['order' => 1, 'sets' => 1, 'reps' => null, 'weight' => null, 'duration_seconds' => 300, 'rest_seconds' => 60, 'rpe' => 4.0],
                                    ],
                                ],
                            ],
                            [
                                'name' => 'Main Block',
                                'order' => 2,
                                'exercises' => [
                                    [
                                        'name' => 'Barbell Bench Press',
                                        'category' => 'Strength',
                                        'muscle_group' => 'Chest',
                                        'equipment' => 'Barbell',
                                        'instructions' => 'Flat bench press.',
                                        'infos' => 'Compound chest exercise.',
                                        'additional_metrics' => ['met_value' => 5.0, 'energy_system' => 'Anaerobic', 'difficulty' => 'advanced'],
                                        'prescription' => ['order' => 1, 'sets' => 4, 'reps' => 8, 'weight' => 80.0, 'duration_seconds' => null, 'rest_seconds' => 120, 'rpe' => 8.0],
                                    ],
                                ],
                            ],
                            [
                                'name' => 'Cool-Down',
                                'order' => 3,
                                'exercises' => [
                                    [
                                        'name' => 'Chest Stretch',
                                        'category' => 'Stretching',
                                        'muscle_group' => 'Chest',
                                        'equipment' => 'Body Only',
                                        'instructions' => 'Gentle chest stretch 30s.',
                                        'infos' => 'Reduces tension.',
                                        'additional_metrics' => ['met_value' => 2.0, 'energy_system' => 'Aerobic', 'difficulty' => 'beginner'],
                                        'prescription' => ['order' => 1, 'sets' => 1, 'reps' => null, 'weight' => null, 'duration_seconds' => 30, 'rest_seconds' => 0, 'rpe' => 2.0],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    private function validJsonWithTwoDays(): string
    {
        $block = [
            'name' => 'Warmup',
            'order' => 1,
            'exercises' => [
                [
                    'name' => 'Light Jog',
                    'category' => 'Cardio',
                    'muscle_group' => 'Full Body',
                    'equipment' => 'Body Only',
                    'instructions' => 'Light jog.',
                    'infos' => 'Warmup.',
                    'additional_metrics' => ['met_value' => 3.0, 'energy_system' => 'Aerobic', 'difficulty' => 'beginner'],
                    'prescription' => ['order' => 1, 'sets' => 1, 'reps' => null, 'weight' => null, 'duration_seconds' => 300, 'rest_seconds' => 30, 'rpe' => 3.0],
                ],
            ],
        ];

        return (string) json_encode([
            'workout_plan' => [
                'training_days_per_week' => 2,
                'goal' => 'strength_building',
                'experience_level' => 'intermediate',
                'workout_type' => 'strength',
                'plan_days' => [
                    ['day_of_week' => 1, 'workout_name' => 'Upper Body Strength', 'duration_minutes' => 60, 'workout_blocks' => [$block]],
                    ['day_of_week' => 3, 'workout_name' => 'Lower Body Strength', 'duration_minutes' => 60, 'workout_blocks' => [$block]],
                ],
            ],
        ]);
    }
}
