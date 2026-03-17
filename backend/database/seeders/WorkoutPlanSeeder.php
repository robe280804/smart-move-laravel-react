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
            ['email' => 'john@example.com'],
            ['name' => 'Test User', 'password' => bcrypt('Password1*!04')],
        );

        $trainingDaysPerWeek = rand(3, 7);

        /** @var WorkoutPlan $plan */
        $plan = $user->workoutPlans()->create([
            'training_days_per_week' => $trainingDaysPerWeek,
            'goal'                   => TrainingGoalType::StrengthBuilding->value,
            'experience_level'       => ExperienceLevel::Intermediate->value,
            'workout_type'           => WorkoutType::Strength->value,
        ]);

        $days = array_slice($this->planDays(), 0, $trainingDaysPerWeek);

        foreach ($days as $dayData) {
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
                'day_of_week'      => 2, // Tuesday
                'workout_name'     => 'Core & Conditioning',
                'duration_minutes' => 45,
                'blocks'           => [
                    [
                        'name'      => 'Warmup',
                        'order'     => 1,
                        'exercises' => [
                            [
                                'name'               => 'Jump Rope',
                                'category'           => 'cardio',
                                'muscle_group'       => 'full body',
                                'equipment'          => 'jump_rope',
                                'instructions'       => 'Keep elbows close to sides, rotate wrists to swing the rope. Land softly on the balls of your feet.',
                                'infos'              => 'Elevates heart rate and warms up the ankles and calves.',
                                'additional_metrics' => ['description' => 'Low-impact cardio warm-up that raises core temperature and activates fast-twitch fibers in the lower leg.', 'met_value' => 10.0, 'energy_system' => 'aerobic', 'difficulty' => 'beginner'],
                                'prescription'       => ['order' => 1, 'duration_seconds' => 120, 'rest_seconds' => 30, 'rpe' => 4.0],
                            ],
                        ],
                    ],
                    [
                        'name'      => 'Main',
                        'order'     => 2,
                        'exercises' => [
                            [
                                'name'               => 'Plank',
                                'category'           => 'isolation',
                                'muscle_group'       => 'core',
                                'equipment'          => 'bodyweight',
                                'instructions'       => 'Forearms on the floor, elbows under shoulders. Keep body in a straight line from head to heels. Brace abs and glutes throughout.',
                                'infos'              => 'Do not let hips sag or pike. Breathe steadily.',
                                'additional_metrics' => ['description' => 'Isometric core stability exercise building anti-extension strength in the trunk and deep abdominal wall.', 'met_value' => 4.0, 'energy_system' => 'anaerobic_alactic', 'difficulty' => 'beginner'],
                                'prescription'       => ['order' => 1, 'sets' => 3, 'duration_seconds' => 60, 'rest_seconds' => 60, 'rpe' => 6.0],
                            ],
                            [
                                'name'               => 'Dead Bug',
                                'category'           => 'isolation',
                                'muscle_group'       => 'core',
                                'equipment'          => 'bodyweight',
                                'instructions'       => 'Lie on back, arms straight up, knees at 90°. Lower opposite arm and leg toward floor while pressing lower back into the ground. Return and alternate.',
                                'infos'              => 'Keep the lower back pressed to the floor throughout the movement.',
                                'additional_metrics' => ['description' => 'Anti-rotation core exercise that develops coordination between limbs while maintaining lumbar stability.', 'met_value' => 3.5, 'energy_system' => 'anaerobic_alactic', 'difficulty' => 'beginner'],
                                'prescription'       => ['order' => 2, 'sets' => 3, 'reps' => 10, 'rest_seconds' => 60, 'rpe' => 5.0],
                            ],
                            [
                                'name'               => 'Russian Twist',
                                'category'           => 'isolation',
                                'muscle_group'       => 'core',
                                'equipment'          => 'dumbbell',
                                'instructions'       => 'Sit with knees bent, lean back slightly. Hold a dumbbell and rotate the torso side to side, touching the weight to the floor each rep.',
                                'infos'              => 'Keep feet off the floor for added difficulty.',
                                'additional_metrics' => ['description' => 'Rotational core exercise targeting the obliques and improving transverse-plane strength and stability.', 'met_value' => 4.5, 'energy_system' => 'anaerobic_alactic', 'difficulty' => 'beginner'],
                                'prescription'       => ['order' => 3, 'sets' => 3, 'reps' => 20, 'weight' => 8.0, 'rest_seconds' => 60, 'rpe' => 6.0],
                            ],
                            [
                                'name'               => 'Ab Wheel Rollout',
                                'category'           => 'compound',
                                'muscle_group'       => 'core',
                                'equipment'          => 'ab_wheel',
                                'instructions'       => 'Kneel on the floor, grip the ab wheel. Roll forward as far as possible while keeping hips low, then pull back using your abs.',
                                'infos'              => 'Start with partial range and progress to full rollout as strength improves.',
                                'additional_metrics' => ['description' => 'High-demand anti-extension core exercise requiring significant abdominal and lat co-activation to control the rollout arc.', 'met_value' => 5.0, 'energy_system' => 'anaerobic_lactic', 'difficulty' => 'intermediate'],
                                'prescription'       => ['order' => 4, 'sets' => 3, 'reps' => 8, 'rest_seconds' => 90, 'rpe' => 7.5],
                            ],
                        ],
                    ],
                    [
                        'name'      => 'Cool-down',
                        'order'     => 3,
                        'exercises' => [
                            [
                                'name'               => "Child's Pose",
                                'category'           => 'mobility',
                                'muscle_group'       => 'spine',
                                'equipment'          => 'bodyweight',
                                'instructions'       => 'Kneel and sit back toward heels. Extend arms forward on the floor and let the lower back decompress. Breathe deeply.',
                                'infos'              => 'Releases lower back and hip flexor tension.',
                                'additional_metrics' => ['description' => "Restorative stretch that decompresses the lumbar spine and calms the nervous system after core-intensive work.", 'met_value' => 1.0, 'energy_system' => 'aerobic', 'difficulty' => 'beginner'],
                                'prescription'       => ['order' => 1, 'duration_seconds' => 60, 'rest_seconds' => 0, 'rpe' => 1.0],
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
                'day_of_week'      => 4, // Thursday
                'workout_name'     => 'Lower Body — Hypertrophy',
                'duration_minutes' => 55,
                'blocks'           => [
                    [
                        'name'      => 'Warmup',
                        'order'     => 1,
                        'exercises' => [
                            [
                                'name'               => 'Monster Walk',
                                'category'           => 'mobility',
                                'muscle_group'       => 'glutes',
                                'equipment'          => 'resistance_band',
                                'instructions'       => 'Place a resistance band around your ankles. Take lateral steps while staying in a half-squat position, keeping the band taut.',
                                'infos'              => 'Activates glute medius and hip abductors before heavy lower body work.',
                                'additional_metrics' => ['description' => 'Hip abductor activation drill that primes the glute medius and stabilizers before hypertrophy-focused lower body training.', 'met_value' => 3.0, 'energy_system' => 'aerobic', 'difficulty' => 'beginner'],
                                'prescription'       => ['order' => 1, 'sets' => 2, 'reps' => 12, 'rest_seconds' => 30, 'rpe' => 3.0],
                            ],
                        ],
                    ],
                    [
                        'name'      => 'Main',
                        'order'     => 2,
                        'exercises' => [
                            [
                                'name'               => 'Leg Press',
                                'category'           => 'compound',
                                'muscle_group'       => 'quadriceps',
                                'equipment'          => 'leg_press_machine',
                                'instructions'       => 'Place feet shoulder-width on the platform. Lower the sled until knees reach 90°, then press back to full extension without locking out.',
                                'infos'              => 'Higher foot placement shifts emphasis to glutes and hamstrings.',
                                'additional_metrics' => ['description' => 'Machine-based quad-dominant compound movement allowing high volume and loading without spinal compression.', 'met_value' => 6.0, 'energy_system' => 'anaerobic_lactic', 'difficulty' => 'beginner'],
                                'prescription'       => ['order' => 1, 'sets' => 4, 'reps' => 12, 'weight' => 150.0, 'rest_seconds' => 120, 'rpe' => 7.0],
                            ],
                            [
                                'name'               => 'Lying Leg Curl',
                                'category'           => 'isolation',
                                'muscle_group'       => 'hamstrings',
                                'equipment'          => 'leg_curl_machine',
                                'instructions'       => 'Lie face down on the machine. Curl the weight toward your glutes in a controlled arc, then lower slowly over 3 seconds.',
                                'infos'              => 'Focus on the eccentric to maximize hamstring hypertrophy.',
                                'additional_metrics' => ['description' => 'Hamstring isolation exercise targeting the knee flexors with consistent tension throughout the range of motion.', 'met_value' => 4.0, 'energy_system' => 'anaerobic_alactic', 'difficulty' => 'beginner'],
                                'prescription'       => ['order' => 2, 'sets' => 4, 'reps' => 12, 'weight' => 40.0, 'rest_seconds' => 90, 'rpe' => 7.0],
                            ],
                            [
                                'name'               => 'Leg Extension',
                                'category'           => 'isolation',
                                'muscle_group'       => 'quadriceps',
                                'equipment'          => 'leg_extension_machine',
                                'instructions'       => 'Sit with the pad just above the ankles. Extend legs to full lockout, hold 1 second at the top, then lower under control.',
                                'infos'              => 'Terminal knee extension at the top maximizes VMO activation.',
                                'additional_metrics' => ['description' => 'Quad isolation exercise providing direct knee extensor stimulus with constant cable tension at longer muscle lengths.', 'met_value' => 3.5, 'energy_system' => 'anaerobic_alactic', 'difficulty' => 'beginner'],
                                'prescription'       => ['order' => 3, 'sets' => 3, 'reps' => 15, 'weight' => 50.0, 'rest_seconds' => 90, 'rpe' => 6.5],
                            ],
                            [
                                'name'               => 'Hip Thrust',
                                'category'           => 'compound',
                                'muscle_group'       => 'glutes',
                                'equipment'          => 'barbell',
                                'instructions'       => 'Upper back on bench, bar across hips. Drive hips up until body forms a straight line from knees to shoulders. Squeeze glutes hard at the top.',
                                'infos'              => 'The most effective exercise for glute hypertrophy. Use a pad for comfort.',
                                'additional_metrics' => ['description' => 'Hip extension pattern producing the highest glute EMG activation of any lower body exercise, ideal for posterior chain hypertrophy.', 'met_value' => 5.5, 'energy_system' => 'anaerobic_lactic', 'difficulty' => 'intermediate'],
                                'prescription'       => ['order' => 4, 'sets' => 4, 'reps' => 10, 'weight' => 80.0, 'rest_seconds' => 120, 'rpe' => 7.5],
                            ],
                        ],
                    ],
                    [
                        'name'      => 'Cool-down',
                        'order'     => 3,
                        'exercises' => [
                            [
                                'name'               => 'Pigeon Pose',
                                'category'           => 'mobility',
                                'muscle_group'       => 'hips',
                                'equipment'          => 'bodyweight',
                                'instructions'       => 'From a push-up position, bring one knee forward behind your wrist. Extend the other leg back and lower your hips to the floor. Hold and breathe deeply.',
                                'infos'              => 'One of the most effective hip flexor and glute stretches.',
                                'additional_metrics' => ['description' => 'Deep hip-opening stretch that targets the piriformis and hip flexors, restoring length after high-volume lower body training.', 'met_value' => 1.5, 'energy_system' => 'aerobic', 'difficulty' => 'beginner'],
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
            [
                'day_of_week'      => 6, // Saturday
                'workout_name'     => 'Full Body — Power',
                'duration_minutes' => 60,
                'blocks'           => [
                    [
                        'name'      => 'Warmup',
                        'order'     => 1,
                        'exercises' => [
                            [
                                'name'               => 'Bodyweight Squat',
                                'category'           => 'compound',
                                'muscle_group'       => 'quadriceps',
                                'equipment'          => 'bodyweight',
                                'instructions'       => 'Stand feet shoulder-width, arms forward. Descend until thighs are parallel, keeping chest up. Drive through heels to stand.',
                                'infos'              => 'Use as a movement quality check before loading.',
                                'additional_metrics' => ['description' => 'Unloaded squat pattern to groove movement mechanics and raise core temperature before power work.', 'met_value' => 3.5, 'energy_system' => 'aerobic', 'difficulty' => 'beginner'],
                                'prescription'       => ['order' => 1, 'sets' => 2, 'reps' => 10, 'rest_seconds' => 30, 'rpe' => 3.0],
                            ],
                        ],
                    ],
                    [
                        'name'      => 'Main',
                        'order'     => 2,
                        'exercises' => [
                            [
                                'name'               => 'Box Jump',
                                'category'           => 'compound',
                                'muscle_group'       => 'quadriceps',
                                'equipment'          => 'plyo_box',
                                'instructions'       => 'Stand in front of a box. Dip into a quarter squat, swing arms, and jump onto the box landing softly with both feet. Step down and reset.',
                                'infos'              => 'Land softly to absorb impact through the ankles, knees, and hips.',
                                'additional_metrics' => ['description' => 'Plyometric power exercise developing explosive lower body force production and reactive strength.', 'met_value' => 8.0, 'energy_system' => 'anaerobic_alactic', 'difficulty' => 'intermediate'],
                                'prescription'       => ['order' => 1, 'sets' => 4, 'reps' => 5, 'rest_seconds' => 120, 'rpe' => 7.0],
                            ],
                            [
                                'name'               => 'Push Press',
                                'category'           => 'compound',
                                'muscle_group'       => 'shoulders',
                                'equipment'          => 'barbell',
                                'instructions'       => 'Bar at shoulder height. Dip at the knees, then drive legs to initiate the press. Lock out overhead and lower the bar back to the rack position.',
                                'infos'              => 'Use leg drive to move heavier loads than a strict press. Keep core braced throughout.',
                                'additional_metrics' => ['description' => 'Power-based overhead pressing movement integrating lower body drive with upper body pressing for full-body force production.', 'met_value' => 7.0, 'energy_system' => 'anaerobic_alactic', 'difficulty' => 'intermediate'],
                                'prescription'       => ['order' => 2, 'sets' => 4, 'reps' => 5, 'weight' => 60.0, 'rest_seconds' => 180, 'rpe' => 8.0],
                            ],
                            [
                                'name'               => 'Trap Bar Deadlift',
                                'category'           => 'compound',
                                'muscle_group'       => 'back',
                                'equipment'          => 'trap_bar',
                                'instructions'       => 'Stand inside the trap bar. Grip the handles, hinge at hips and bend knees, then drive through the floor to full hip extension. Lower with control.',
                                'infos'              => 'Allows a more upright torso than a conventional deadlift, reducing lower back stress.',
                                'additional_metrics' => ['description' => 'Full-body pulling pattern that combines elements of deadlift and squat mechanics, ideal for power output and total body loading.', 'met_value' => 8.5, 'energy_system' => 'anaerobic_lactic', 'difficulty' => 'intermediate'],
                                'prescription'       => ['order' => 3, 'sets' => 4, 'reps' => 5, 'weight' => 120.0, 'rest_seconds' => 240, 'rpe' => 8.5],
                            ],
                            [
                                'name'               => 'Battle Rope Slam',
                                'category'           => 'cardio',
                                'muscle_group'       => 'full body',
                                'equipment'          => 'battle_ropes',
                                'instructions'       => 'Hold both rope ends overhead, then slam them to the floor with maximum force. Reset quickly and repeat.',
                                'infos'              => 'Maximally explosive effort on every rep. Allow no passive recovery between slams.',
                                'additional_metrics' => ['description' => 'High-intensity full-body conditioning movement developing power endurance and metabolic conditioning.', 'met_value' => 9.0, 'energy_system' => 'anaerobic_lactic', 'difficulty' => 'intermediate'],
                                'prescription'       => ['order' => 4, 'sets' => 3, 'duration_seconds' => 30, 'rest_seconds' => 90, 'rpe' => 9.0],
                            ],
                        ],
                    ],
                    [
                        'name'      => 'Cool-down',
                        'order'     => 3,
                        'exercises' => [
                            [
                                'name'               => 'Foam Roll — Full Body',
                                'category'           => 'mobility',
                                'muscle_group'       => 'full body',
                                'equipment'          => 'foam_roller',
                                'instructions'       => 'Slowly roll over the quads, hamstrings, glutes, upper back, and lats. Pause on tight spots for 20–30 seconds.',
                                'infos'              => 'Apply moderate pressure — never roll directly over joints.',
                                'additional_metrics' => ['description' => 'Self-myofascial release protocol to reduce muscle tension and accelerate recovery after a high-intensity full body session.', 'met_value' => 2.0, 'energy_system' => 'aerobic', 'difficulty' => 'beginner'],
                                'prescription'       => ['order' => 1, 'duration_seconds' => 300, 'rest_seconds' => 0, 'rpe' => 2.0],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'day_of_week'      => 7, // Sunday
                'workout_name'     => 'Active Recovery & Mobility',
                'duration_minutes' => 40,
                'blocks'           => [
                    [
                        'name'      => 'Mobility Flow',
                        'order'     => 1,
                        'exercises' => [
                            [
                                'name'               => "World's Greatest Stretch",
                                'category'           => 'mobility',
                                'muscle_group'       => 'full body',
                                'equipment'          => 'bodyweight',
                                'instructions'       => 'Step into a lunge, place same-side hand on the floor, rotate the opposite arm to the sky. Then bring elbow toward the floor. Alternate sides.',
                                'infos'              => 'One movement that addresses hip flexors, thoracic rotation, and hamstring length simultaneously.',
                                'additional_metrics' => ['description' => 'Multi-joint mobility drill integrating hip, thoracic, and hamstring flexibility in a single flowing movement pattern.', 'met_value' => 3.0, 'energy_system' => 'aerobic', 'difficulty' => 'beginner'],
                                'prescription'       => ['order' => 1, 'sets' => 2, 'reps' => 5, 'rest_seconds' => 30, 'rpe' => 2.0],
                            ],
                            [
                                'name'               => 'Thoracic Spine Rotation',
                                'category'           => 'mobility',
                                'muscle_group'       => 'spine',
                                'equipment'          => 'bodyweight',
                                'instructions'       => 'Lie on your side with knees stacked at 90°. Reach top arm forward on the floor, then rotate it open toward the ceiling, following with your eyes.',
                                'infos'              => 'Keep hips stacked and still to isolate thoracic mobility.',
                                'additional_metrics' => ['description' => 'Isolated thoracic rotation drill countering the cumulative stiffness from heavy pressing and pulling sessions during the week.', 'met_value' => 2.0, 'energy_system' => 'aerobic', 'difficulty' => 'beginner'],
                                'prescription'       => ['order' => 2, 'sets' => 2, 'reps' => 10, 'rest_seconds' => 30, 'rpe' => 2.0],
                            ],
                            [
                                'name'               => 'Deep Squat Hold',
                                'category'           => 'mobility',
                                'muscle_group'       => 'hips',
                                'equipment'          => 'bodyweight',
                                'instructions'       => 'Lower into the deepest squat you can manage with heels on the floor. Hold and breathe. Use hands on knees to gently push knees out.',
                                'infos'              => 'Improves ankle dorsiflexion, hip internal rotation, and groin flexibility.',
                                'additional_metrics' => ['description' => 'Full-depth squat hold targeting ankle, knee, and hip mobility simultaneously, reinforcing patterns needed for performance and injury prevention.', 'met_value' => 2.0, 'energy_system' => 'aerobic', 'difficulty' => 'beginner'],
                                'prescription'       => ['order' => 3, 'sets' => 3, 'duration_seconds' => 60, 'rest_seconds' => 30, 'rpe' => 2.5],
                            ],
                            [
                                'name'               => 'Supine Hamstring Stretch',
                                'category'           => 'mobility',
                                'muscle_group'       => 'hamstrings',
                                'equipment'          => 'bodyweight',
                                'instructions'       => 'Lie on your back, loop a band or towel around one foot. Straighten the leg and gently pull toward your chest until you feel a stretch. Hold and breathe.',
                                'infos'              => 'Keep the opposite leg flat on the floor to isolate the target hamstring.',
                                'additional_metrics' => ['description' => 'Static hamstring flexibility drill to restore posterior chain length after a week of heavy hip hinge and squat loading.', 'met_value' => 1.5, 'energy_system' => 'aerobic', 'difficulty' => 'beginner'],
                                'prescription'       => ['order' => 4, 'duration_seconds' => 60, 'rest_seconds' => 15, 'rpe' => 1.5],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
