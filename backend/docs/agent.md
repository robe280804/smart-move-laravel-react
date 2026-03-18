# Fitness Agent — Architecture & Flow Documentation

## Overview

The Fitness Agent is an AI-powered system that generates personalized workout plans through a
**NeuronAI Workflow** pipeline. It combines a **RAG pipeline** (Retrieval-Augmented Generation
backed by Qdrant) with a **three-node stateful workflow** to collect user data, retrieve relevant
exercises, and produce a structured PHP typed output that is persisted asynchronously.

The architecture is **single-request, non-conversational**: the frontend sends all user
preferences in one validated request; the controller immediately returns `HTTP 202 Accepted`
with a pending plan, while a **queued job** runs the workflow in the background and populates
the plan when complete. The client polls `GET /api/v1/workout-plans/{id}` to check the status.

---

## Components

| Component                | Class                                      | Role                                                                        |
| ------------------------ | ------------------------------------------ | --------------------------------------------------------------------------- |
| HTTP entry point         | `AgentController`                          | Validates request, creates pending plan, dispatches job, returns 202        |
| Async job                | `GenerateWorkoutPlanJob`                   | Runs workflow in background; marks plan processing → completed / failed     |
| Workflow orchestrator    | `FitnessAgentWorkflow`                     | Sequences the three nodes; uses EloquentPersistence for resumable state     |
| Node 1 — validate        | `InitialNode`                              | Validates required state keys; emits `SanitizeInputEvent`                   |
| Node 2 — profile         | `CollectUserInfosNode`                     | Reads `FitnessInfo` from DB (cached 10 min); sets `fitness_data` on state   |
| Node 3 — generation      | `GenerateWorkoutPlanNode`                  | Parallel RAG retrieval + structured LLM call; sets `agent_response` on state|
| Conversational agent     | `FitnessAgent`                             | Anthropic Claude (temperature 0.3); returns structured output               |
| RAG agent                | `FitnessAgentRag`                          | Embeds queries and retrieves exercises from Qdrant (topK 15 per query)      |
| RAG agent (filtered)     | `FitnessAgentRagFiltered`                  | Extends `FitnessAgentRag`; swaps vector store for `FilterableQdrantVectorStore` with an equipment payload filter |
| Filtered vector store    | `FilterableQdrantVectorStore`              | Extends `QdrantVectorStore`; injects an optional Qdrant `filter` block into `points/query` requests |
| Plan persistence         | `WorkoutPlanService::fillFromAgentResponse`| Parses agent JSON, updates plan fields, creates all related DB models       |
| Workflow state store     | `WorkflowInterruptRecord`                  | Eloquent model backing `EloquentPersistence` for workflow state             |

**Infrastructure:**

- **Anthropic Claude** — LLM for plan generation (`services.claude.key` / `services.claude.model`, max 16 000 tokens, temperature 0.3)
- **Ollama** — `nomic-embed-text` embedding model (768-dim) used by `FitnessAgentRag` only
- **Qdrant** — vector store, collection `exercises`, Cosine distance, HNSW index, topK: 15 per query

---

## Phase 1 — Exercise Catalogue Ingestion (One-Time)

Before the agent can work, the exercise knowledge base must be loaded into Qdrant.
This is a one-time (or periodic) operation triggered manually or via an Artisan command.

```
CSV file (exercise-gym-dataset)
        │
        ▼
WorkflowCsvToQdrant::run(string $csvPath)
        │
        ├── buildDocuments()          reads rows, maps fields
        │     │
        │     └── mapRowToDocument()  builds NeuronAI Document with:
        │           ├── content       "Exercise: {name}. Description: {desc}. Type: … Level: …"
        │           └── metadata
        │                 ├── name
        │                 ├── category          (CATEGORY_MAP normalization)
        │                 ├── equipment         (lowercased)
        │                 ├── primary-muscle    (lowercased)
        │                 ├── secondary_muscle
        │                 ├── difficulty        (DIFFICULTY_MAP normalization)
        │                 └── energy_system     (ENERGY_SYSTEM_MAP normalization)
        │
        └── FitnessAgentRag::addDocuments($chunk, 50)
              │
              ├── OllamaEmbeddingsProvider embeds each document (nomic-embed-text)
              └── QdrantVectorStore stores vector + payload
```

**Category normalisation applied at ingestion:**

| CSV `Type`            | Stored `category` | `energy_system`   |
| --------------------- | ----------------- | ----------------- |
| Strength              | `strength`        | `phosphocreatine` |
| Cardio                | `cardio`          | `oxidative`       |
| Stretching            | `mobility`        | `none`            |
| Plyometrics           | `plyometric`      | `phosphocreatine` |
| Olympic Weightlifting | `strength`        | `phosphocreatine` |
| Powerlifting          | `strength`        | `phosphocreatine` |
| Strongman             | `strength`        | `phosphocreatine` |

**Payload schema indexed in Qdrant** (keyword filters available):
`equipment` · `primary-muscle` · `category` · `difficulty` · `energy_system` · `secondary_muscle`

---

## Phase 2 — HTTP Request & Async Dispatch

### 2a. Entry Point

```
User (HTTP POST /api/v1/agent/generate-workout)   [auth:sanctum + ACCESS_API ability]
        │
        ▼
AgentCallRequest (validate + pre-sanitize free-text fields)
        │
        ▼
AgentController::generateWorkout()
        │
        ├── WorkoutPlanService::createPending($user)
        │     └── INSERT workout_plans (user_id, status='pending')
        │           → WorkoutPlan { id, status: pending }
        │
        ├── GenerateWorkoutPlanJob::dispatch($plan, $user, $workflowState)
        │     workflowState = {
        │       user_id, user_email,
        │       fitness_goals, schedule, equipment, constraints, preferences
        │     }
        │
        └── return ApiSuccess(WorkoutPlanResource, HTTP 202 Accepted)
              → { data: { id, status: "pending", plan_days: [] } }
```

### 2b. Validated Request Fields (`AgentCallRequest`)

| Field                    | Type       | Rules                          |
| ------------------------ | ---------- | ------------------------------ |
| `fitness_goals`          | `string[]` | required, 1-3 items            |
| `training_days_per_week` | `int`      | required, 1-7                  |
| `available_days`         | `string[]` | required, min 1                |
| `session_duration`       | `int`      | required, 15-180 minutes       |
| `injuries`               | `string`   | nullable, max 500 chars        |
| `equipment`              | `string[]` | required, min 1                |
| `gym_access`             | `bool`     | required                       |
| `workout_type`           | `string[]` | required, 1-3 items            |
| `sports`                 | `string`   | nullable, max 500 chars        |
| `preferred_exercises`    | `string`   | nullable, max 500 chars        |
| `additional_notes`       | `string`   | nullable, max 1 000 chars      |

### 2c. Plan Status Lifecycle

```
pending  ──►  processing  ──►  completed
                  │
                  └──►  failed
```

| Status       | Set by                                          | Meaning                                     |
| ------------ | ----------------------------------------------- | ------------------------------------------- |
| `pending`    | `WorkoutPlanService::createPending()`           | Plan created, job not yet picked up         |
| `processing` | `GenerateWorkoutPlanJob::handle()` (start)      | Workflow is running                         |
| `completed`  | `WorkoutPlanService::fillFromAgentResponse()`   | Plan fully populated with exercises         |
| `failed`     | `GenerateWorkoutPlanJob::failed(\Throwable $e)` | Workflow or persistence threw an exception  |

The client polls `GET /api/v1/workout-plans/{id}` and checks `data.status` until it is
`completed` or `failed`.

---

## Phase 3 — Async Job Execution

`GenerateWorkoutPlanJob` implements `ShouldQueue` with:
- `$timeout = 600` seconds (Anthropic calls can take up to 10 minutes for complex plans)
- `$tries = 1` (no automatic retries — AI generation is expensive and failures are not transient)

```
GenerateWorkoutPlanJob::handle(WorkoutPlanService $service)
        │
        ├── $plan->update(['status' => processing])
        │
        ├── Reconstruct WorkflowState from $workflowState array
        │
        ├── FitnessAgentWorkflow::create(state: $state)->init()->run()
        │     (see Phase 4 for workflow internals)
        │
        └── WorkoutPlanService::fillFromAgentResponse($plan, $state->get('agent_response'))

GenerateWorkoutPlanJob::failed(\Throwable $e)
        └── $plan->update(['status' => failed])
```

---

## Phase 4 — NeuronAI Workflow Execution

The workflow is managed by `FitnessAgentWorkflow`, which uses `EloquentPersistence`
backed by `WorkflowInterruptRecord` to support interruptible/resumable execution.

```
FitnessAgentWorkflow::nodes()
    ├── InitialNode            (listens on StartEvent)
    ├── CollectUserInfosNode   (listens on SanitizeInputEvent)
    └── GenerateWorkoutPlanNode (listens on UserInfosCollectedEvent)
```

### Node 1 — InitialNode

Validates that all required state keys are present before the workflow proceeds.
Throws `\InvalidArgumentException` if any key is missing.
Required keys: `user_id`, `fitness_goals`, `schedule`, `equipment`.

### Node 2 — CollectUserInfosNode

```
SanitizeInputEvent received
        │
        └── Cache::remember("fitness_profile:{user_id}", 10 min)
              └── UserRepositoryInterface::findById(state.user_id)
                    └── ->fitnessInfo()->firstOrFail()
                          │
                          └── sets state.fitness_data = {
                                age, height, weight, gender, experience_level
                              }
        │
        └── emits UserInfosCollectedEvent
```

Physical profile (age, weight, height, gender, experience_level) is always read from the
`fitness_info` table; the user never types these during plan generation. The result is cached
for 10 minutes so that repeated generations within the same session skip the DB query.

### Node 3 — GenerateWorkoutPlanNode

See `docs/rag-retrieval.md` for the base retrieval strategy. The node now runs up to **5 parallel queries**, each targeting a distinct semantic angle, with optional Qdrant payload filtering for equipment-constrained users.

#### Query strategy

| # | Always? | Query focus | Vector store |
|---|---------|-------------|--------------|
| 1 | Yes | Goal + experience level (+ "injury safe" signal when constraints present) | `FitnessAgentRag` |
| 2 | When equipment or workout_type present | Equipment items + workout types + "exercises" | `FitnessAgentRagFiltered` (equipment `should` filter) if home user; `FitnessAgentRag` otherwise |
| 3 | Yes | Gym compound/isolation OR bodyweight calisthenics depending on `gym_access` | `FitnessAgentRag` |
| 4 | When `sports` field provided | Sport-specific functional exercises | `FitnessAgentRag` |
| 5 | When injury keywords detected in constraints | Rehabilitation / injury-safe exercises | `FitnessAgentRag` |

#### Equipment payload filter (Query 2, home users)

When the user has no gym access, `buildEquipmentFilter()` maps frontend equipment labels to the Qdrant vocabulary (lowercased at ingestion) and builds a Qdrant `should` (OR) filter on the `equipment` payload field. Bodyweight is always appended so exercises requiring no equipment remain eligible. Gym-access users skip the filter because the full catalogue is available to them.

```
EQUIPMENT_KB_MAP (frontend label → KB vocabulary):
  Dumbbells → dumbbell  |  Barbells → barbell  |  Resistance Bands → resistance band
  Pull-up Bar → pull-up bar  |  Kettlebells → kettlebell  |  Cable Machine → cable machine
  Cardio Equipment → cardio  |  Bodyweight Only → bodyweight
  (Bench and "everything" excluded — too broad to filter)

Qdrant filter shape:
  { "should": [
      { "key": "equipment", "match": { "value": "dumbbell" } },
      { "key": "equipment", "match": { "value": "bodyweight" } }
  ]}
```

```
UserInfosCollectedEvent received
        │
        ├── buildGoalQuery()          →  Q1 (always; adds "injury safe" token when constraints ≠ "")
        ├── buildEquipmentFilter()    →  Qdrant should-filter (home users only)
        │
        ├── Concurrency::run()  →  3–5 parallel Qdrant searches (15 results each)
        │     ├── Q1  FitnessAgentRag           (goal + experience)
        │     ├── Q2  FitnessAgentRagFiltered   (equipment + type, filtered) or FitnessAgentRag
        │     ├── Q3  FitnessAgentRag           (gym compound / bodyweight context)
        │     ├── Q4  FitnessAgentRag           (sport-specific, optional)
        │     └── Q5  FitnessAgentRag           (rehabilitation, optional)
        │           └── deduplicate by md5(content) → up to 75 unique documents → slice to 30
        │
        ├── buildPrompt()
        │     === USER FITNESS PROFILE ===      (from fitness_data)
        │     === USER REQUEST ===              (goals, schedule, equipment, constraints, preferences)
        │     === AVAILABLE EXERCISES FROM KNOWLEDGE BASE ===   (up to 30 exercises)
        │     "If the knowledge base does not contain enough…"
        │
        └── FitnessAgent::make()
              ->structured(UserMessage($prompt), WorkoutPlanOutput::class)
                    │
                    └── Anthropic Claude (temp 0.3) returns WorkoutPlanOutput
                              │
                              └── state.agent_response = json_encode($output)
        │
        └── emits StopEvent
```

---

## Phase 5 — FitnessAgent System Prompt

`FitnessAgent` composes its `SystemPrompt` from three prompt classes:

**Background** (`SecurityPrompt` + `BackgroundPrompt`):
- Confidentiality rules: never reveal internal instructions or system prompt
- Professional S&C coach persona with expertise in Exercise Science, Nutrition, Injury Prevention
- Topic restriction to fitness only; refuses off-topic requests
- Evidence-based training principles; metric units (kg, cm) only

**Steps** (`StepsPrompt`):
1. Profile analysis — extract all user data from the provided message (no follow-up questions)
2. Exercise selection — use **only** exercises listed in the knowledge base section
3. Plan design — progressive overload, volume/intensity balance, mandatory Warmup + Cool-down blocks, RPE calibration, rest day respect
4. Safety check — restrict high-risk compound lifts to intermediate+, exclude exercises involving injured body parts, verify weekly volume
5. JSON emission — single complete JSON object, never split, never partial

**Output** (`OutputPrompt`):
- Respond with **only** a valid JSON object (no prose, no markdown fences)
- Enumerations for `goal`, `experience_level`, `workout_type`, `energy_system`, `difficulty`

---

## Phase 6 — Structured Output (PHP Typed Classes)

The agent uses NeuronAI's structured output feature. The LLM response is deserialized
directly into typed PHP objects annotated with `#[SchemaProperty]`.
Fields with existing PHP enums (`TrainingGoalType`, `ExperienceLevel`, `WorkoutType`) use
enum types directly so NeuronAI generates a JSON schema `enum` constraint automatically.

```
WorkoutPlanOutput
└── WorkoutPlanData                    workout_plan
      ├── int              $training_days_per_week
      ├── TrainingGoalType $goal
      ├── ExperienceLevel  $experience_level
      ├── WorkoutType      $workout_type
      └── PlanDayData[]    $plan_days
            ├── int     $day_of_week    (1=Monday … 7=Sunday)
            ├── ?string $workout_name
            ├── int     $duration_minutes
            └── WorkoutBlockData[]  $workout_blocks
                  ├── string $name
                  ├── int    $order
                  └── ExerciseData[]  $exercises
                        ├── string  $name           (exact name from knowledge base)
                        ├── string  $category
                        ├── ?string $muscle_group
                        ├── ?string $equipment
                        ├── ?string $instructions
                        ├── ?string $infos
                        ├── ?AdditionalMetricsData $additional_metrics
                        │     ├── ?string $description
                        │     ├── ?float  $met_value
                        │     ├── ?string $energy_system   (aerobic | anaerobic_lactic | anaerobic_alactic | mixed)
                        │     └── ?string $difficulty      (beginner | intermediate | advanced | professional)
                        └── PrescriptionData $prescription
                              ├── int    $order
                              ├── ?int   $sets
                              ├── ?int   $reps
                              ├── ?float $weight            (kg; null for bodyweight)
                              ├── ?int   $duration_seconds  (null for rep-based)
                              ├── int    $rest_seconds
                              └── float  $rpe               (1.0–10.0)
```

---

## Phase 7 — Async Persistence

Persistence happens inside `GenerateWorkoutPlanJob`, inside `WorkoutPlanService::fillFromAgentResponse`.

```
fillFromAgentResponse(WorkoutPlan $plan, string $jsonResponse)
        │
        ├── parseJsonResponse()
        │     ├── strips markdown fences if present (```json … ```)
        │     ├── json_decode() → array
        │     └── asserts 'workout_plan' key exists
        │
        └── DB::transaction()
              │
              ├── $plan->update(training_days_per_week, goal, experience_level, workout_type, status=completed)
              │
              ├── foreach plan_days
              │     └── $plan->planDays()->create()   → PlanDay
              │
              ├── foreach workout_blocks
              │     └── $planDay->workoutBlocks()->create()  → WorkoutBlock
              │
              └── foreach exercises
                    ├── Exercise::query()->create()          → Exercise  (always new)
                    └── $block->blockExercises()->create()   → BlockExercise
        │
        └── return $plan->load('planDays.workoutBlocks.blockExercises.exercise')
```

> **Note:** Exercises are always created fresh per plan (`create()`, not `firstOrCreate()`).
> Deduplication across plans is not currently applied.

---

## Mapping: Structured Output → Database Models

| Structured Output field                    | Target model    | Target column                                                                |
| ------------------------------------------ | --------------- | ---------------------------------------------------------------------------- |
| `workout_plan.training_days_per_week`      | `WorkoutPlan`   | `training_days_per_week`                                                     |
| `workout_plan.goal`                        | `WorkoutPlan`   | `goal` (enum `TrainingGoalType`)                                             |
| `workout_plan.experience_level`            | `WorkoutPlan`   | `experience_level` (enum `ExperienceLevel`)                                  |
| `workout_plan.workout_type`                | `WorkoutPlan`   | `workout_type`                                                               |
| `plan_days[].day_of_week`                  | `PlanDay`       | `day_of_week`                                                                |
| `plan_days[].workout_name`                 | `PlanDay`       | `workout_name`                                                               |
| `plan_days[].duration_minutes`             | `PlanDay`       | `duration_minutes`                                                           |
| `workout_blocks[].name`                    | `WorkoutBlock`  | `name`                                                                       |
| `workout_blocks[].order`                   | `WorkoutBlock`  | `order`                                                                      |
| `exercises[].name`                         | `Exercise`      | `name`                                                                       |
| `exercises[].category`                     | `Exercise`      | `category`                                                                   |
| `exercises[].muscle_group`                 | `Exercise`      | `muscle_group`                                                               |
| `exercises[].equipment`                    | `Exercise`      | `equipment`                                                                  |
| `exercises[].instructions`                 | `Exercise`      | `instructions`                                                               |
| `exercises[].infos`                        | `Exercise`      | `infos`                                                                      |
| `exercises[].additional_metrics`           | `Exercise`      | `additional_metrics` (JSON cast to array)                                    |
| `exercises[].prescription.*`               | `BlockExercise` | `order`, `sets`, `reps`, `weight`, `duration_seconds`, `rest_seconds`, `rpe` |

---

## Full End-to-End Flow Summary

```
[One-time]
CSV dataset ──► WorkflowCsvToQdrant ──► Qdrant vector store

[Per user request]
POST /api/v1/agent/generate-workout
    │
    ▼
AgentCallRequest (validate + sanitize free-text fields)
    │
    ▼
AgentController::generateWorkout()
    │
    ├── WorkoutPlanService::createPending($user)  →  WorkoutPlan { status: pending }
    ├── GenerateWorkoutPlanJob::dispatch($plan, $user, $workflowState)
    └── return HTTP 202 Accepted  { data: { id, status: "pending" } }

[Queue worker — background]
    │
    ▼
GenerateWorkoutPlanJob::handle()
    │
    ├── $plan->update(status: processing)
    │
    ▼
FitnessAgentWorkflow::create(state)->init()->run()
    │
    ├── InitialNode
    │     └── validates required keys, emits SanitizeInputEvent
    │
    ├── CollectUserInfosNode
    │     ├── Cache::remember(fitness_profile:{id}, 10 min)
    │     │     └── Reads FitnessInfo from DB
    │     ├── Sets state.fitness_data
    │     └── emits UserInfosCollectedEvent
    │
    └── GenerateWorkoutPlanNode
          ├── buildGoalQuery()          →  Q1 (+ injury-safe signal if constraints present)
          ├── buildEquipmentFilter()    →  Qdrant should-filter for home users
          ├── Concurrency::run()        →  3–5 parallel Qdrant searches (15 each)
          │     Q1 goal+exp | Q2 equipment+type (filtered) | Q3 gym/bodyweight
          │     Q4 sports (optional) | Q5 rehabilitation (optional)
          ├── deduplicate + slice to 30 exercises
          ├── buildPrompt()
          ├── FitnessAgent::structured(prompt, WorkoutPlanOutput::class)  [Claude, temp 0.3]
          └── state.agent_response = json_encode(WorkoutPlanOutput)
    │
    ▼
WorkoutPlanService::fillFromAgentResponse($plan, state.agent_response)
    │
    ├── Parse JSON → array
    └── DB::transaction()
          $plan->update(fields + status: completed)
          PlanDay → WorkoutBlock → Exercise (create) + BlockExercise

[Client polling]
GET /api/v1/workout-plans/{id}
    └── { data: { status: "pending" | "processing" | "completed" | "failed", ... } }
```
