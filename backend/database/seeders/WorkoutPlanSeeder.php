<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ExperienceLevel;
use App\Enums\TrainingGoalType;
use App\Enums\WorkoutType;
use App\Models\User;
use App\Models\WorkoutPlan;
use Illuminate\Database\Seeder;

class WorkoutPlanSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => bcrypt('password')],
        );

        /** @var WorkoutPlan $plan */
        $plan = $user->workoutPlans()->create([
            'training_days_per_week' => 3,
            'goal'                   => TrainingGoalType::StrengthBuilding->value,
            'experience_level'       => ExperienceLevel::Intermediate->value,
            'workout_type'           => WorkoutType::Strength->value,
        ]);

        foreach ($this->planDays() as $dayData) {
            $planDay = $plan->planDays()->create([
                'day_of_week'      => $dayData['day_of_week'],
                'workout_name'     => $dayData['workout_name'],
                'duration_minutes' => $dayData['duration_minutes'],
            ]);

            foreach ($dayData['blocks'] as $blockData) {
                $block = $planDay->workoutBlocks()->create([
                    'name'  => $blockData['name'],
                    'order' => $blockData['order'],
                ]);

                foreach ($blockData['exercises'] as $exerciseData) {
                    $exercise = \App\Models\Exercise::query()->create([
                        'name'               => $exerciseData['name'],
                        'category'           => $exerciseData['category'],
                        'muscle_group'       => $exerciseData['muscle_group'] ?? null,
                        'equipment'          => $exerciseData['equipment'] ?? null,
                        'instructions'       => $exerciseData['instructions'] ?? null,
                        'infos'              => $exerciseData['infos'] ?? null,
                        'additional_metrics' => $exerciseData['additional_metrics'] ?? null,
                    ]);

                    $block->blockExercises()->create([
                        'exercise_id'      => $exercise->id,
                        'order'            => $exerciseData['prescription']['order'],
                        'sets'             => $exerciseData['prescription']['sets'] ?? null,
                        'reps'             => $exerciseData['prescription']['reps'] ?? null,
                        'weight'           => $exerciseData['prescription']['weight'] ?? null,
                        'duration_seconds' => $exerciseData['prescription']['duration_seconds'] ?? null,
                        'rest_seconds'     => $exerciseData['prescription']['rest_seconds'],
                        'rpe'              => $exerciseData['prescription']['rpe'],
                    ]);
                }
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function planDays(): array
    {
        return [
            [
                'day_of_week'      => 1, // Monday
                'workout_name'     => 'Upper Body — Push',
                'duration_minutes' => 60,
                'blocks'           => [
                    [
                        'name'      => 'Warmup',
                        'order'     => 1,
                        'exercises' => [
                            [
                                'name'               => 'Arm Circles',
                                'category'           => 'mobility',
                                'muscle_group'       => 'shoulders',
                                'equipment'          => 'bodyweight',
                                'instructions'       => 'Stand with feet shoulder-width apart. Extend arms out and rotate in large circles forward then backward.',
                                'infos'              => 'Loosens shoulder joints before pressing movements.',
                                'additional_metrics' => ['description' => 'Dynamic shoulder warm-up to increase joint mobility and blood flow before push work.', 'met_value' => 2.5, 'energy_system' => 'aerobic', 'difficulty' => 'beginner'],
                                'prescription'       => ['order' => 1, 'sets' => 2, 'reps' => 15, 'rest_seconds' => 30, 'rpe' => 2.0],
                            ],
                            [
                                'name'               => 'Band Pull-Apart',
                                'category'           => 'mobility',
                                'muscle_group'       => 'upper back',
                                'equipment'          => 'resistance_band',
                                'instructions'       => 'Hold a band at chest height with arms extended. Pull the band apart until it touches your chest, squeezing the shoulder blades.',
                                'infos'              => 'Activates rear deltoids and external rotators before pressing.',
                                'additional_metrics' => ['description' => 'Shoulder health drill that activates the posterior shoulder girdle, reducing impingement risk on pressing days.', 'met_value' => 2.0, 'energy_system' => 'aerobic', 'difficulty' => 'beginner'],
                                'prescription'       => ['order' => 2, 'sets' => 3, 'reps' => 15, 'rest_seconds' => 30, 'rpe' => 2.5],
                            ],
                        ],
                    ],
                    [
                        'name'      => 'Main',
                        'order'     => 2,
                        'exercises' => [
                            [
                                'name'               => 'Barbell Bench Press',
                                'category'           => 'compound',
                                'muscle_group'       => 'chest',
                                'equipment'          => 'barbell',
                                'instructions'       => 'Lie flat on bench. Grip bar slightly wider than shoulder-width. Lower bar to mid-chest under control, then press to full lockout.',
                                'infos'              => 'Primary horizontal push pattern. Keep shoulder blades retracted and feet flat throughout.',
                                'additional_metrics' => ['description' => 'Primary upper body strength movement targeting the pectorals, anterior deltoids, and triceps through a horizontal push pattern.', 'met_value' => 6.0, 'energy_system' => 'anaerobic_lactic', 'difficulty' => 'intermediate'],
                                'prescription'       => ['order' => 1, 'sets' => 4, 'reps' => 6, 'weight' => 80.0, 'rest_seconds' => 180, 'rpe' => 8.0],
                            ],
                            [
                                'name'               => 'Overhead Press',
                                'category'           => 'compound',
                                'muscle_group'       => 'shoulders',
                                'equipment'          => 'barbell',
                                'instructions'       => 'Stand with bar at collar-bone height. Press bar overhead to full lockout, keeping core braced and glutes tight.',
                                'infos'              => 'Key vertical push movement for shoulder and upper body strength.',
                                'additional_metrics' => ['description' => 'Vertical pressing pattern that develops deltoid and tricep strength while requiring full-body stability.', 'met_value' => 5.5, 'energy_system' => 'anaerobic_lactic', 'difficulty' => 'intermediate'],
                                'prescription'       => ['order' => 2, 'sets' => 4, 'reps' => 8, 'weight' => 50.0, 'rest_seconds' => 150, 'rpe' => 7.5],
                            ],
                            [
                                'name'               => 'Incline Dumbbell Press',
                                'category'           => 'compound',
                                'muscle_group'       => 'chest',
                                'equipment'          => 'dumbbell',
                                'instructions'       => 'Set bench to 30–45°. Press dumbbells from shoulder height to full lockout, then lower under control.',
                                'infos'              => 'Targets upper chest and provides greater range of motion than the barbell press.',
                                'additional_metrics' => ['description' => 'Accessory pressing movement with emphasis on the clavicular head of the pectorals and greater shoulder stability demand.', 'met_value' => 5.0, 'energy_system' => 'anaerobic_lactic', 'difficulty' => 'intermediate'],
                                'prescription'       => ['order' => 3, 'sets' => 3, 'reps' => 10, 'weight' => 28.0, 'rest_seconds' => 120, 'rpe' => 7.0],
                            ],
                            [
                                'name'               => 'Tricep Rope Pushdown',
                                'category'           => 'isolation',
                                'muscle_group'       => 'triceps',
                                'equipment'          => 'cable_machine',
                                'instructions'       => 'Attach rope to high pulley. Grip rope with palms facing in. Push down until arms are fully extended, spreading the rope at the bottom.',
                                'infos'              => 'Isolates all three heads of the tricep. Keep elbows pinned to your sides.',
                                'additional_metrics' => ['description' => 'Tricep isolation exercise to increase arm hypertrophy and reinforce lockout strength for pressing movements.', 'met_value' => 3.5, 'energy_system' => 'anaerobic_alactic', 'difficulty' => 'beginner'],
                                'prescription'       => ['order' => 4, 'sets' => 3, 'reps' => 12, 'rest_seconds' => 90, 'rpe' => 6.5],
                            ],
                        ],
                    ],
                    [
                        'name'      => 'Cool-down',
                        'order'     => 3,
                        'exercises' => [
                            [
                                'name'               => 'Doorway Chest Stretch',
                                'category'           => 'mobility',
                                'muscle_group'       => 'chest',
                                'equipment'          => 'bodyweight',
                                'instructions'       => 'Place forearms on a doorframe and lean forward until you feel a stretch across the chest. Hold 30 seconds each side.',
                                'infos'              => 'Counteracts pectoral tightness after heavy pressing.',
                                'additional_metrics' => ['description' => 'Static stretch to restore chest and anterior shoulder length after a push-focused session.', 'met_value' => 1.5, 'energy_system' => 'aerobic', 'difficulty' => 'beginner'],
                                'prescription'       => ['order' => 1, 'duration_seconds' => 60, 'rest_seconds' => 15, 'rpe' => 1.5],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'day_of_week'      => 3, // Wednesday
                'workout_name'     => 'Lower Body — Strength',
                'duration_minutes' => 65,
                'blocks'           => [
                    [
                        'name'      => 'Warmup',
                        'order'     => 1,
                        'exercises' => [
                            [
                                'name'               => 'Hip Circle',
                                'category'           => 'mobility',
                                'muscle_group'       => 'hips',
                                'equipment'          => 'bodyweight',
                                'instructions'       => 'Stand on one leg and draw large circles with the raised knee, moving the hip through its full range.',
                                'infos'              => 'Opens the hip joint before squatting and hinging movements.',
                                'additional_metrics' => ['description' => 'Dynamic hip mobility drill that lubricates the hip joint and activates the glutes before lower body loading.', 'met_value' => 2.5, 'energy_system' => 'aerobic', 'difficulty' => 'beginner'],
                                'prescription'       => ['order' => 1, 'sets' => 2, 'reps' => 10, 'rest_seconds' => 30, 'rpe' => 2.0],
                            ],
                            [
                                'name'               => 'Glute Bridge',
                                'category'           => 'isolation',
                                'muscle_group'       => 'glutes',
                                'equipment'          => 'bodyweight',
                                'instructions'       => 'Lie on your back with knees bent and feet flat. Drive hips up until body forms a straight line from shoulders to knees. Squeeze at the top.',
                                'infos'              => 'Activates glutes before squats and deadlifts.',
                                'additional_metrics' => ['description' => 'Glute activation pattern essential for ensuring the posterior chain fires first in compound lower body movements.', 'met_value' => 3.0, 'energy_system' => 'aerobic', 'difficulty' => 'beginner'],
                                'prescription'       => ['order' => 2, 'sets' => 3, 'reps' => 15, 'rest_seconds' => 30, 'rpe' => 2.5],
                            ],
                        ],
                    ],
                    [
                        'name'      => 'Main',
                        'order'     => 2,
                        'exercises' => [
                            [
                                'name'               => 'Barbell Back Squat',
                                'category'           => 'compound',
                                'muscle_group'       => 'quadriceps',
                                'equipment'          => 'barbell',
                                'instructions'       => 'Bar on upper traps. Feet shoulder-width, toes slightly out. Break at hips and knees simultaneously. Descend until thighs are parallel to floor. Drive through mid-foot to stand.',
                                'infos'              => 'King of lower body movements. Maintain neutral spine and knees tracking over toes throughout.',
                                'additional_metrics' => ['description' => 'Primary lower body strength movement loading the quadriceps, glutes, and entire posterior chain under maximal mechanical tension.', 'met_value' => 8.0, 'energy_system' => 'anaerobic_lactic', 'difficulty' => 'intermediate'],
                                'prescription'       => ['order' => 1, 'sets' => 4, 'reps' => 5, 'weight' => 100.0, 'rest_seconds' => 240, 'rpe' => 8.5],
                            ],
                            [
                                'name'               => 'Romanian Deadlift',
                                'category'           => 'compound',
                                'muscle_group'       => 'hamstrings',
                                'equipment'          => 'barbell',
                                'instructions'       => 'Stand hip-width. Hinge at hips, pushing them back as bar slides down thighs. Lower until you feel a deep hamstring stretch. Return by driving hips forward.',
                                'infos'              => 'Prioritize hip hinge quality over load. Shoulders stay above or slightly in front of hips.',
                                'additional_metrics' => ['description' => 'Hip hinge pattern targeting the hamstrings and glutes eccentrically, key for posterior chain development and injury resilience.', 'met_value' => 6.5, 'energy_system' => 'anaerobic_lactic', 'difficulty' => 'intermediate'],
                                'prescription'       => ['order' => 2, 'sets' => 4, 'reps' => 8, 'weight' => 80.0, 'rest_seconds' => 180, 'rpe' => 7.5],
                            ],
                            [
                                'name'               => 'Bulgarian Split Squat',
                                'category'           => 'compound',
                                'muscle_group'       => 'quadriceps',
                                'equipment'          => 'dumbbell',
                                'instructions'       => 'Rear foot elevated on bench. Front foot forward enough so knee stays behind toes at bottom. Lower until rear knee nearly touches floor.',
                                'infos'              => 'Excellent unilateral leg developer. Expose and correct strength imbalances between legs.',
                                'additional_metrics' => ['description' => 'Single-leg strength exercise correcting left-right imbalances while developing quad, glute, and hip flexor strength.', 'met_value' => 6.0, 'energy_system' => 'anaerobic_lactic', 'difficulty' => 'intermediate'],
                                'prescription'       => ['order' => 3, 'sets' => 3, 'reps' => 10, 'weight' => 20.0, 'rest_seconds' => 120, 'rpe' => 7.0],
                            ],
                            [
                                'name'               => 'Leg Press Calf Raise',
                                'category'           => 'isolation',
                                'muscle_group'       => 'calves',
                                'equipment'          => 'leg_press_machine',
                                'instructions'       => 'Place balls of feet at the bottom edge of the sled. Lower heels as far as possible, then press up onto toes. Full range of motion each rep.',
                                'infos'              => 'Use slow eccentric (3 seconds down) for maximum calf stimulus.',
                                'additional_metrics' => ['description' => 'Isolated calf hypertrophy work on the leg press, allowing greater loading than standing calf raises with controlled range of motion.', 'met_value' => 3.0, 'energy_system' => 'anaerobic_alactic', 'difficulty' => 'beginner'],
                                'prescription'       => ['order' => 4, 'sets' => 4, 'reps' => 15, 'weight' => 120.0, 'rest_seconds' => 90, 'rpe' => 6.0],
                            ],
                        ],
                    ],
                    [
                        'name'      => 'Cool-down',
                        'order'     => 3,
                        'exercises' => [
                            [
                                'name'               => 'Standing Quad Stretch',
                                'category'           => 'mobility',
                                'muscle_group'       => 'quadriceps',
                                'equipment'          => 'bodyweight',
                                'instructions'       => 'Stand on one leg, pull the opposite heel toward your glutes. Keep knees together and stand tall.',
                                'infos'              => 'Releases quad tension after squatting volume.',
                                'additional_metrics' => ['description' => 'Static quadriceps stretch to reduce post-session DOMS and restore hip flexor length after heavy squat work.', 'met_value' => 1.5, 'energy_system' => 'aerobic', 'difficulty' => 'beginner'],
                                'prescription'       => ['order' => 1, 'duration_seconds' => 60, 'rest_seconds' => 15, 'rpe' => 1.5],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'day_of_week'      => 5, // Friday
                'workout_name'     => 'Upper Body — Pull',
                'duration_minutes' => 60,
                'blocks'           => [
                    [
                        'name'      => 'Warmup',
                        'order'     => 1,
                        'exercises' => [
                            [
                                'name'               => 'Cat-Cow Stretch',
                                'category'           => 'mobility',
                                'muscle_group'       => 'spine',
                                'equipment'          => 'bodyweight',
                                'instructions'       => 'On hands and knees. Inhale and arch back (cow). Exhale and round spine toward ceiling (cat). Alternate slowly.',
                                'infos'              => 'Mobilizes the thoracic spine before pulling movements.',
                                'additional_metrics' => ['description' => 'Spinal mobility drill that prepares the thoracic region for the rowing and pulling demands of a back-focused session.', 'met_value' => 2.0, 'energy_system' => 'aerobic', 'difficulty' => 'beginner'],
                                'prescription'       => ['order' => 1, 'sets' => 2, 'reps' => 10, 'rest_seconds' => 30, 'rpe' => 1.5],
                            ],
                        ],
                    ],
                    [
                        'name'      => 'Main',
                        'order'     => 2,
                        'exercises' => [
                            [
                                'name'               => 'Weighted Pull-Up',
                                'category'           => 'compound',
                                'muscle_group'       => 'back',
                                'equipment'          => 'pull_up_bar',
                                'instructions'       => 'Grip bar slightly wider than shoulder-width, palms away. Hang fully, then drive elbows down and back until chin clears bar. Lower under full control.',
                                'infos'              => 'Add weight via belt or vest. If unable to complete reps with added weight, use bodyweight.',
                                'additional_metrics' => ['description' => 'Vertical pull pattern developing latissimus dorsi width, bicep, and grip strength — cornerstone of any pulling program.', 'met_value' => 8.0, 'energy_system' => 'anaerobic_lactic', 'difficulty' => 'intermediate'],
                                'prescription'       => ['order' => 1, 'sets' => 4, 'reps' => 6, 'weight' => 10.0, 'rest_seconds' => 180, 'rpe' => 8.5],
                            ],
                            [
                                'name'               => 'Barbell Bent-Over Row',
                                'category'           => 'compound',
                                'muscle_group'       => 'back',
                                'equipment'          => 'barbell',
                                'instructions'       => 'Hinge to ~45° torso angle, bar hanging at arm\'s length. Pull bar to lower sternum, driving elbows back. Lower under control.',
                                'infos'              => 'Keep lower back flat throughout. Do not use momentum to swing the bar.',
                                'additional_metrics' => ['description' => 'Horizontal pulling pattern that builds mid-back thickness, lat strength, and postural resilience to counter excessive pressing volume.', 'met_value' => 6.5, 'energy_system' => 'anaerobic_lactic', 'difficulty' => 'intermediate'],
                                'prescription'       => ['order' => 2, 'sets' => 4, 'reps' => 8, 'weight' => 70.0, 'rest_seconds' => 150, 'rpe' => 7.5],
                            ],
                            [
                                'name'               => 'Seated Cable Row',
                                'category'           => 'compound',
                                'muscle_group'       => 'back',
                                'equipment'          => 'cable_machine',
                                'instructions'       => 'Sit upright with slight forward lean. Pull handle to abdomen, driving elbows back and squeezing shoulder blades together. Return with control.',
                                'infos'              => 'Focus on the peak contraction — pause 1 second with elbows fully behind torso.',
                                'additional_metrics' => ['description' => 'Accessory horizontal pull reinforcing scapular retraction and mid-back development with constant cable tension.', 'met_value' => 5.0, 'energy_system' => 'anaerobic_lactic', 'difficulty' => 'intermediate'],
                                'prescription'       => ['order' => 3, 'sets' => 3, 'reps' => 10, 'weight' => 60.0, 'rest_seconds' => 120, 'rpe' => 7.0],
                            ],
                            [
                                'name'               => 'Dumbbell Bicep Curl',
                                'category'           => 'isolation',
                                'muscle_group'       => 'biceps',
                                'equipment'          => 'dumbbell',
                                'instructions'       => 'Stand with dumbbells at sides, palms facing forward. Curl both weights to shoulder height, keeping elbows pinned. Lower slowly over 3 seconds.',
                                'infos'              => 'Avoid swinging torso to lift the weight. Control the eccentric for greater bicep hypertrophy.',
                                'additional_metrics' => ['description' => 'Bicep isolation to develop arm hypertrophy and reinforce elbow flexor strength for pulling movements.', 'met_value' => 3.5, 'energy_system' => 'anaerobic_alactic', 'difficulty' => 'beginner'],
                                'prescription'       => ['order' => 4, 'sets' => 3, 'reps' => 12, 'weight' => 14.0, 'rest_seconds' => 90, 'rpe' => 6.5],
                            ],
                        ],
                    ],
                    [
                        'name'      => 'Cool-down',
                        'order'     => 3,
                        'exercises' => [
                            [
                                'name'               => 'Lat Stretch on Pull-Up Bar',
                                'category'           => 'mobility',
                                'muscle_group'       => 'back',
                                'equipment'          => 'pull_up_bar',
                                'instructions'       => 'Hang from the bar with straight arms. Relax the shoulders and let body weight create a decompression stretch through the lats and spine.',
                                'infos'              => 'Effective spinal decompression and lat lengthening after heavy pulling work.',
                                'additional_metrics' => ['description' => 'Passive hang stretch that decompresses the spine and restores lat length after vertical and horizontal pulling volume.', 'met_value' => 1.5, 'energy_system' => 'aerobic', 'difficulty' => 'beginner'],
                                'prescription'       => ['order' => 1, 'duration_seconds' => 45, 'rest_seconds' => 15, 'rpe' => 1.5],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
