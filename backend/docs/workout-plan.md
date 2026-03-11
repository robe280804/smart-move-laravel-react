# Workout Plan — Data Model Documentation

## Overview

A **Workout Plan** is the top-level schedule assigned to a user. It is composed of a
4-level hierarchy:

```
WorkoutPlan
└── PlanDay          (one day of the week)
    └── WorkoutBlock (a named phase within that day)
        └── BlockExercise  (pivot: exercise + prescription)
            └── Exercise   (catalogue entry)
```

Each level has a single responsibility:

| Level           | Responsibility                                      |
| --------------- | --------------------------------------------------- |
| `WorkoutPlan`   | User goal, frequency, and experience context        |
| `PlanDay`       | Which day of the week, session name, total duration |
| `WorkoutBlock`  | Named phase of the session (Warmup, Strength, …)    |
| `BlockExercise` | The prescription for one exercise inside a block    |
| `Exercise`      | Reusable catalogue entry (movement definition)      |

---

## Models

### 1. `WorkoutPlan`

**Table:** `workout_plans`

The root of the schedule. One user can have more plan ( based on the subscription ). It defines the broad
parameters the AI agent used to generate it.

| Column                      | Type         | Nullable | Notes                                                 |
| --------------------------- | ------------ | -------- | ----------------------------------------------------- |
| `id`                        | bigint PK    | —        |                                                       |
| `user_id`                   | FK → `users` | —        | Cascade delete                                        |
| `training_days_per_week`    | integer      | —        | 1–7                                                   |
| `goal`                      | enum         | —        | See `TrainingGoalType`                                |
| `experience_level`          | enum         | —        | See `ExperienceLevel`                                 |
| `workout_type`              | string       | —        | e.g. `strength`, `sprint`, `mobility`, `conditioning` |
| `created_at` / `updated_at` | timestamps   | —        |                                                       |

**Enums:**

`TrainingGoalType`: `weight_loss` · `muscle_gain` · `endurance` · `flexibility` · `strength_building` · `general_fitness`

`ExperienceLevel`: `beginner` · `intermediate` · `advanced` · `professional`

**Relations:**

- `user()` → `BelongsTo User`
- `planDays()` → `HasMany PlanDay`

**Example:**

```json
{
    "id": 1,
    "user_id": 42,
    "training_days_per_week": 4,
    "goal": "strength_building",
    "experience_level": "intermediate",
    "workout_type": "strength"
}
```

---

### 2. `PlanDay`

**Table:** `plan_days`

One entry per scheduled training day. `day_of_week` uses ISO convention:
`1 = Monday … 7 = Sunday`. A rest day is expressed by the absence of a
`PlanDay` record for that day number, or by a day with no blocks.

| Column                      | Type                 | Nullable | Notes                                    |
| --------------------------- | -------------------- | -------- | ---------------------------------------- |
| `id`                        | bigint PK            | —        |                                          |
| `workout_plan_id`           | FK → `workout_plans` | —        | Cascade delete                           |
| `day_of_week`               | integer              | —        | 1 (Mon) – 7 (Sun)                        |
| `workout_name`              | string               | ✓        | e.g. `Pull`, `Legs`, `Sprint Max Effort` |
| `duration_minutes`          | integer              | —        | Estimated total session length           |
| `created_at` / `updated_at` | timestamps           | —        |                                          |

**Relations:**

- `workoutPlan()` → `BelongsTo WorkoutPlan`
- `workoutBlocks()` → `HasMany WorkoutBlock`

**Example:**

```json
{
    "id": 10,
    "workout_plan_id": 1,
    "day_of_week": 1,
    "workout_name": "Upper Body — Push",
    "duration_minutes": 75
}
```

---

### 3. `WorkoutBlock`

**Table:** `workout_blocks`

A named phase inside a single session. Blocks are ordered within a day and
allow the session to be split into logical segments (Warmup → Main → Accessory → Core).

| Column                      | Type             | Nullable | Notes                                                                |
| --------------------------- | ---------------- | -------- | -------------------------------------------------------------------- |
| `id`                        | bigint PK        | —        |                                                                      |
| `plan_day_id`               | FK → `plan_days` | —        | Cascade delete                                                       |
| `name`                      | string           | —        | e.g. `Warmup`, `Strength`, `Accessory`, `Sprint`, `Mobility`, `Core` |
| `order`                     | integer          | —        | Rendering order within the day (1-based)                             |
| `created_at` / `updated_at` | timestamps       | —        |                                                                      |

**Relations:**

- `planDay()` → `BelongsTo PlanDay`
- `blockExercises()` → `HasMany BlockExercise`
- `exercises()` → `BelongsToMany Exercise` via `block_exercises` (with pivot)

**Example:**

```json
{
    "id": 100,
    "plan_day_id": 10,
    "name": "Strength",
    "order": 2
}
```

---

### 4. `BlockExercise` (Pivot)

**Table:** `block_exercises`

The join between a `WorkoutBlock` and an `Exercise`. This is where the
**prescription** lives: how many sets, reps, load, duration, rest, and
perceived effort are assigned to the movement for this specific block.

All prescription columns are nullable because different exercise types
use different parameters (e.g. a sprint uses `duration_seconds`, not `reps`).

| Column                      | Type                  | Nullable | Notes                                        |
| --------------------------- | --------------------- | -------- | -------------------------------------------- |
| `id`                        | bigint PK             | —        |                                              |
| `workout_block_id`          | FK → `workout_blocks` | —        | Cascade delete                               |
| `exercise_id`               | FK → `exercises`      | —        | No cascade (exercises are catalogue entries) |
| `order`                     | integer               | ✓        | Execution order within the block             |
| `sets`                      | integer               | ✓        | Number of sets                               |
| `reps`                      | integer               | ✓        | Reps per set                                 |
| `weight`                    | decimal(6,2)          | ✓        | Load in kg                                   |
| `duration_seconds`          | integer               | ✓        | For timed efforts (sprints, planks, …)       |
| `rest_seconds`              | integer               | ✓        | Rest between sets                            |
| `rpe`                       | decimal(3,1)          | ✓        | Rate of Perceived Exertion (0–10 scale)      |
| `created_at` / `updated_at` | timestamps            | —        |                                              |

**Relations:**

- `workoutBlock()` → `BelongsTo WorkoutBlock`
- `exercise()` → `BelongsTo Exercise`

**Example — strength lift:**

```json
{
    "id": 500,
    "workout_block_id": 100,
    "exercise_id": 7,
    "order": 1,
    "sets": 4,
    "reps": 6,
    "weight": 80.0,
    "duration_seconds": null,
    "rest_seconds": 180,
    "rpe": 8.0
}
```

**Example — timed sprint:**

```json
{
    "id": 501,
    "workout_block_id": 101,
    "exercise_id": 22,
    "order": 1,
    "sets": 6,
    "reps": null,
    "weight": null,
    "duration_seconds": 30,
    "rest_seconds": 90,
    "rpe": 9.5
}
```

---

### 5. `Exercise`

**Table:** `exercises`

A reusable catalogue entry that describes a movement. It is not tied to any
specific plan — many blocks across many plans can reference the same exercise.

| Column                      | Type       | Nullable | Notes                                                              |
| --------------------------- | ---------- | -------- | ------------------------------------------------------------------ |
| `id`                        | bigint PK  | —        |                                                                    |
| `category`                  | string     | —        | `strength` · `sprint` · `mobility` · `conditioning`                |
| `muscle_group`              | string     | ✓        | Primary muscle target (e.g. `chest`, `back`, `legs`, `core`)       |
| `equipment`                 | string     | ✓        | `barbell` · `dumbbell` · `kettlebell` · `bodyweight` · `machine`   |
| `instructions`              | text       | ✓        | Step-by-step execution cues                                        |
| `infos`                     | text       | ✓        | General descriptive information, coaching notes, contraindications |
| `additional_metrics`        | json       | ✓        | Structured performance data (e.g. MET value, calories/min)         |
| `created_at` / `updated_at` | timestamps | —        |                                                                    |

**Casts:** `additional_metrics` → `array`

**Relations:**

- `blockExercises()` → `HasMany BlockExercise`
- `workoutBlocks()` → `BelongsToMany WorkoutBlock` via `block_exercises`

**Example:**

```json
{
    "id": 7,
    "category": "strength",
    "muscle_group": "chest",
    "equipment": "barbell",
    "instructions": "Lie supine on bench. Grip bar slightly wider than shoulder-width. Lower to chest under control, then press to lockout.",
    "infos": "The barbell bench press is a foundational horizontal push pattern. Keep shoulder blades retracted and depressed throughout the lift to protect the shoulder joint.",
    "additional_metrics": {
        "met_value": 6.0,
        "calories_burned_per_minute": 8.5
    }
}
```

---

## Entity Relationship Diagram

```
users
  │
  └─< workout_plans          (user_id FK)
        │
        └─< plan_days         (workout_plan_id FK)
              │
              └─< workout_blocks  (plan_day_id FK)
                    │
                    └─< block_exercises  (workout_block_id FK)
                          │
                          └── exercises  (exercise_id FK)
```

---

## Complete Example — Full Workout Plan

Below is a full example for an intermediate user with a `strength_building` goal,
training 4 days per week. Only **Monday (day 1)** is expanded to show the full hierarchy.

```json
{
    "id": 1,
    "user_id": 42,
    "training_days_per_week": 4,
    "goal": "strength_building",
    "experience_level": "intermediate",
    "workout_type": "strength",
    "plan_days": [
        {
            "id": 10,
            "day_of_week": 1,
            "workout_name": "Upper Body — Push",
            "duration_minutes": 75,
            "workout_blocks": [
                {
                    "id": 100,
                    "name": "Warmup",
                    "order": 1,
                    "block_exercises": [
                        {
                            "order": 1,
                            "sets": 2,
                            "reps": 12,
                            "weight": null,
                            "duration_seconds": null,
                            "rest_seconds": 60,
                            "rpe": 4.0,
                            "exercise": {
                                "id": 1,
                                "category": "mobility",
                                "muscle_group": "shoulders",
                                "equipment": "bodyweight",
                                "instructions": "Arm circles, band pull-aparts.",
                                "infos": "Activates the rotator cuff and scapular stabilisers before heavy pressing.",
                                "additional_metrics": null
                            }
                        }
                    ]
                },
                {
                    "id": 101,
                    "name": "Strength",
                    "order": 2,
                    "block_exercises": [
                        {
                            "order": 1,
                            "sets": 4,
                            "reps": 6,
                            "weight": 80.0,
                            "duration_seconds": null,
                            "rest_seconds": 180,
                            "rpe": 8.0,
                            "exercise": {
                                "id": 7,
                                "category": "strength",
                                "muscle_group": "chest",
                                "equipment": "barbell",
                                "instructions": "Lie supine on bench. Lower bar to chest under control, press to lockout.",
                                "infos": "Primary horizontal push. Keep shoulder blades retracted throughout.",
                                "additional_metrics": {
                                    "met_value": 6.0,
                                    "calories_burned_per_minute": 8.5
                                }
                            }
                        },
                        {
                            "order": 2,
                            "sets": 4,
                            "reps": 6,
                            "weight": 60.0,
                            "duration_seconds": null,
                            "rest_seconds": 180,
                            "rpe": 7.5,
                            "exercise": {
                                "id": 8,
                                "category": "strength",
                                "muscle_group": "shoulders",
                                "equipment": "barbell",
                                "instructions": "Standing overhead press. Brace core, press bar from collar bone to lockout.",
                                "infos": "Vertical push pattern. Avoid hyperextending the lumbar spine.",
                                "additional_metrics": {
                                    "met_value": 5.5,
                                    "calories_burned_per_minute": 7.8
                                }
                            }
                        }
                    ]
                },
                {
                    "id": 102,
                    "name": "Accessory",
                    "order": 3,
                    "block_exercises": [
                        {
                            "order": 1,
                            "sets": 3,
                            "reps": 12,
                            "weight": 22.5,
                            "duration_seconds": null,
                            "rest_seconds": 90,
                            "rpe": 7.0,
                            "exercise": {
                                "id": 15,
                                "category": "strength",
                                "muscle_group": "arms",
                                "equipment": "dumbbell",
                                "instructions": "Seated incline dumbbell curl. Full range of motion at the elbow.",
                                "infos": "Isolates the long head of the biceps due to the incline angle.",
                                "additional_metrics": null
                            }
                        }
                    ]
                },
                {
                    "id": 103,
                    "name": "Core",
                    "order": 4,
                    "block_exercises": [
                        {
                            "order": 1,
                            "sets": 3,
                            "reps": null,
                            "weight": null,
                            "duration_seconds": 45,
                            "rest_seconds": 60,
                            "rpe": 6.5,
                            "exercise": {
                                "id": 30,
                                "category": "conditioning",
                                "muscle_group": "core",
                                "equipment": "bodyweight",
                                "instructions": "Forearm plank. Neutral spine, posterior pelvic tilt, brace 360°.",
                                "infos": "Isometric anti-extension drill. Focus on breath control.",
                                "additional_metrics": {
                                    "met_value": 3.5,
                                    "calories_burned_per_minute": 4.2
                                }
                            }
                        }
                    ]
                }
            ]
        },
        {
            "id": 11,
            "day_of_week": 3,
            "workout_name": "Lower Body — Squat",
            "duration_minutes": 80,
            "workout_blocks": ["..."]
        },
        {
            "id": 12,
            "day_of_week": 5,
            "workout_name": "Upper Body — Pull",
            "duration_minutes": 70,
            "workout_blocks": ["..."]
        },
        {
            "id": 13,
            "day_of_week": 6,
            "workout_name": "Lower Body — Hinge",
            "duration_minutes": 75,
            "workout_blocks": ["..."]
        }
    ]
}
```
