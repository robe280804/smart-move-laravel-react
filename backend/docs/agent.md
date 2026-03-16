# Fitness Agent — Architecture & Flow Documentation

## Overview

The Fitness Agent is an AI-powered system that generates personalized workout plans through a
**NeuronAI Workflow** pipeline. It combines a **RAG pipeline** (Retrieval-Augmented Generation
backed by Qdrant) with a **three-node stateful workflow** to collect user data, retrieve relevant
exercises, and produce a structured PHP typed output that is persisted synchronously.

The architecture is **single-request, non-conversational**: the frontend sends all user
preferences in one validated request; the workflow runs to completion within the same HTTP
request and returns the persisted plan immediately.

---

## Components

| Component                | Class                                    | Role                                                              |
| ------------------------ | ---------------------------------------- | ----------------------------------------------------------------- |
| HTTP entry point         | `AgentController`                        | Validates request, builds WorkflowState, runs workflow, persists  |
| Workflow orchestrator    | `FitnessAgentWorkflow`                   | Sequences the three nodes; uses EloquentPersistence for resumable state |
| Node 1 — sanitize        | `InitialNode`                            | Entry point; emits `SanitizeInputEvent`                           |
| Node 2 — profile         | `CollectUserInfosNode`                   | Reads `FitnessInfo` from DB; sets `fitness_data` on state         |
| Node 3 — generation      | `GenerateWorkoutPlanNode`                | RAG retrieval + structured LLM call; sets `agent_response` on state |
| Conversational agent     | `FitnessAgent`                           | Anthropic Claude agent with system prompt; returns structured output |
| RAG agent                | `FitnessAgentRag`                        | Embeds queries and retrieves exercises from Qdrant                |
| Data ingestion           | `WorkflowCsvToQdrant`                    | One-time seeding of the exercise vector store                     |
| Plan persistence         | `WorkoutPlanService::createFromAgentResponse` | Parses agent JSON and creates all DB models synchronously    |
| Workflow state store     | `WorkflowInterruptRecord`                | Eloquent model backing `EloquentPersistence` for workflow state   |

**Infrastructure:**

- **Anthropic Claude** — LLM for plan generation (`services.claude.key` / `services.claude.model`, max 16 000 tokens)
- **Ollama** — `nomic-embed-text` embedding model (768-dim) used by `FitnessAgentRag` only
- **Qdrant** — vector store, collection `exercises`, Cosine distance, HNSW index, topK: 30

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

## Phase 2 — HTTP Request & WorkflowState Population

### 2a. Entry Point

```
User (HTTP POST /api/v1/agent/generate-workout)   [auth:sanctum + ACCESS_API ability]
        │
        ▼
AgentController::generateWorkout(AgentCallRequest $request)
        │
        ├── AgentCallRequest validates and pre-sanitizes the payload
        │     Sanitized free-text fields: injuries, sports, preferred_exercises, additional_notes
        │
        ├── WorkflowState populated:
        │     user_id, user_email
        │     fitness_goals        — string[]  (1-3 items)
        │     schedule             — { training_days_per_week, available_days, session_duration }
        │     equipment            — { items: string[], gym_access: bool }
        │     constraints          — string|null   (injuries/limitations)
        │     preferences          — { workout_types, sports, preferred_exercises, additional_notes }
        │
        └── FitnessAgentWorkflow::create(state: $state)->init()->run()
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

---

## Phase 3 — NeuronAI Workflow Execution

The workflow is managed by `FitnessAgentWorkflow`, which uses `EloquentPersistence`
backed by `WorkflowInterruptRecord` to support interruptible/resumable execution.

```
FitnessAgentWorkflow::nodes()
    ├── InitialNode            (listens on StartEvent)
    ├── CollectUserInfosNode   (listens on SanitizeInputEvent)
    └── GenerateWorkoutPlanNode (listens on UserInfosCollectedEvent)
```

### Node 1 — InitialNode

Receives the `StartEvent`, emits `SanitizeInputEvent` (no-op placeholder for future input
sanitization).

### Node 2 — CollectUserInfosNode

```
SanitizeInputEvent received
        │
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
`fitness_info` table; the user never types these during plan generation.

### Node 3 — GenerateWorkoutPlanNode

```
UserInfosCollectedEvent received
        │
        ├── Build retrieval query from fitness_data
        │     e.g. "intermediate male 175 80 30 fitness workout exercises"
        │               │
        │               ▼
        │     FitnessAgentRag::make()
        │         ->resolveRetrieval()
        │         ->retrieve(UserMessage $query)
        │               │
        │               ├── OllamaEmbeddingsProvider (nomic-embed-text, 768-dim)
        │               └── QdrantVectorStore.search(vector) → top 30 documents
        │                         │
        │                         └── sliced to top 20 (TOP_K_EXERCISES)
        │
        ├── buildPrompt() — assembles sections:
        │     === USER FITNESS PROFILE ===      (from fitness_data)
        │     === USER REQUEST ===              (goals, schedule, equipment, constraints, preferences)
        │     === AVAILABLE EXERCISES FROM KNOWLEDGE BASE ===   (formatted document list)
        │     "If the knowledge base does not contain enough suitable exercises…"
        │
        └── FitnessAgent::make()
              ->structured(UserMessage($prompt), WorkoutPlanOutput::class)
                    │
                    └── Anthropic Claude returns WorkoutPlanOutput (PHP typed object)
                              │
                              └── state.agent_response = json_encode($output)
        │
        └── emits StopEvent
```

---

## Phase 4 — FitnessAgent System Prompt

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

## Phase 5 — Structured Output (PHP Typed Classes)

The agent uses NeuronAI's structured output feature. The LLM response is deserialized
directly into typed PHP objects annotated with `#[SchemaProperty]`.

```
WorkoutPlanOutput
└── WorkoutPlanData                    workout_plan
      ├── int    $training_days_per_week
      ├── string $goal
      ├── string $experience_level
      ├── string $workout_type
      └── PlanDayData[]  $plan_days
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

After workflow completion the output is serialized:
```php
$state->set('agent_response', json_encode($output));   // WorkoutPlanOutput → JSON string
```

---

## Phase 6 — Synchronous Persistence

Persistence happens **synchronously** in the controller, immediately after workflow completion.
There is no queued job.

```
AgentController
        │
        ├── $state->get('agent_response')  — JSON string from workflow
        │
        └── WorkoutPlanService::createFromAgentResponse(string $jsonResponse, User $user)
                │
                ├── parseJsonResponse()
                │     ├── strips markdown fences if present (```json … ```)
                │     ├── json_decode() → array
                │     └── asserts 'workout_plan' key exists
                │
                └── DB::transaction()
                      │
                      ├── WorkoutPlanRepository::create()   → WorkoutPlan
                      │
                      ├── foreach plan_days
                      │     └── $workoutPlan->planDays()->create()   → PlanDay
                      │
                      ├── foreach workout_blocks
                      │     └── $planDay->workoutBlocks()->create()  → WorkoutBlock
                      │
                      └── foreach exercises
                            ├── Exercise::query()->create()          → Exercise  (always new)
                            └── $block->blockExercises()->create()   → BlockExercise
                │
                └── return WorkoutPlan::load('planDays.workoutBlocks.blockExercises.exercise')
        │
        └── return ApiSuccess(WorkoutPlanResource, HTTP 201)
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
    ├── Build WorkflowState (user_id, fitness_goals, schedule, equipment, constraints, preferences)
    │
    ▼
FitnessAgentWorkflow::create(state)->init()->run()
    │
    ├── InitialNode
    │     └── emits SanitizeInputEvent
    │
    ├── CollectUserInfosNode
    │     ├── Reads FitnessInfo from DB (age, height, weight, gender, experience_level)
    │     ├── Sets state.fitness_data
    │     └── emits UserInfosCollectedEvent
    │
    └── GenerateWorkoutPlanNode
          ├── Build retrieval query from fitness_data
          ├── FitnessAgentRag → Qdrant (top 30) → slice to top 20
          ├── buildPrompt() → USER FITNESS PROFILE + USER REQUEST + KNOWLEDGE BASE exercises
          ├── FitnessAgent::structured(prompt, WorkoutPlanOutput::class)   [Anthropic Claude]
          └── state.agent_response = json_encode(WorkoutPlanOutput)
    │
    ▼
WorkoutPlanService::createFromAgentResponse(state.agent_response, $user)
    │
    ├── Parse JSON → array
    └── DB::transaction()
          WorkoutPlan → PlanDay → WorkoutBlock → Exercise (create) + BlockExercise
    │
    ▼
ApiSuccess(WorkoutPlanResource, HTTP 201)
```
