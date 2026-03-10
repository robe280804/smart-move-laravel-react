# Fitness Agent — Architecture & Flow Documentation

## Overview

The Fitness Agent is an AI-powered conversational system that generates personalized
workout plans. It combines a **RAG pipeline** (Retrieval-Augmented Generation backed
by Qdrant) with a **stateful conversation loop** to collect user information, then
produces a structured JSON plan that maps directly to the database models and is
persisted asynchronously via a queued Job.

---

## Components

| Component | Class | Role |
|---|---|---|
| Conversational agent | `FitnessAgent` | Drives the chat, enforces topic scope, detects readiness |
| RAG agent | `FitnessAgentRag` | Embeds queries and retrieves exercises from Qdrant |
| Data ingestion | `WorkflowCsvToQdrant` | One-time seeding of the exercise vector store |
| HTTP entry point | `AgentController` | Receives user messages, returns agent replies |
| Async persistence | `PersistWorkoutPlanJob` _(planned)_ | Maps the JSON plan to Eloquent models and saves |

**Infrastructure:**

- **Ollama** — local LLM + `nomic-embed-text` embedding model (768-dim)
- **Qdrant** — vector store, collection `exercises`, Cosine distance, HNSW index

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
        │                 └── energy_sistem     (ENERGY_SYSTEM_MAP normalization)
        │
        └── FitnessAgentRag::addDocuments($chunk, 50)
              │
              ├── OllamaEmbeddingsProvider embeds each document (nomic-embed-text)
              └── QdrantVectorStore stores vector + payload
```

**Category normalisation applied at ingestion:**

| CSV `Type` | Stored `category` | `energy_sistem` |
|---|---|---|
| Strength | `strength` | `phosphocreatine` |
| Cardio | `cardio` | `oxidative` |
| Stretching | `mobility` | `none` |
| Plyometrics | `plyometric` | `phosphocreatine` |
| Olympic Weightlifting | `strength` | `phosphocreatine` |
| Powerlifting | `strength` | `phosphocreatine` |
| Strongman | `strength` | `phosphocreatine` |

**Payload schema indexed in Qdrant** (keyword filters available):
`equipment` · `primary-muscle` · `category` · `difficulty` · `energy_sistem` · `secondary_muscle`

---

## Phase 2 — Conversational Plan Generation

### 2a. Conversation Loop

The user interacts with `FitnessAgent` through `AgentController::call()`.
The agent is **topic-scoped** to fitness only and will refuse off-topic requests.

```
User (HTTP POST /api/v1/agent/chat)
        │
        ▼
AgentController::call(Request $request)
        │
        └── FitnessAgent::chat(UserMessage)
              │
              ├── System prompt enforces:
              │     - Professional S&C coach persona
              │     - Evidence-based principles
              │     - Metric units (kg, cm)
              │     - Topic restriction to fitness
              │
              └── Agent reply → ApiSuccess response
```

### 2b. Required Information Gate

The agent **must not generate a plan** until all of the following information
has been collected from the conversation. If any field is missing, the agent
asks the user for it before proceeding.

| Category | Required fields |
|---|---|
| **Physical profile** | Age, weight (kg), height (cm), gender |
| **Fitness profile** | Experience level, current fitness goal |
| **Schedule** | Training days per week, available days of the week, session duration (minutes) |
| **Constraints** | Rest days, any injuries or physical limitations |
| **Equipment** | Available equipment or gym access |
| **Preferences** | Preferred workout type (strength / cardio / mobility / conditioning) |

If any field from the table above is absent from the conversation context,
the agent responds with a clarifying question and waits. **The JSON plan is
never emitted mid-conversation.**

### 2c. RAG Retrieval for Exercise Selection

Once all required information is collected, the agent uses `FitnessAgentRag`
to retrieve relevant exercises from Qdrant before composing the plan.

```
Agent (plan generation phase)
        │
        ├── Embeds a retrieval query per block type
        │     e.g. "strength barbell chest intermediate"
        │               │
        │               ▼
        │     OllamaEmbeddingsProvider (nomic-embed-text, 768-dim)
        │               │
        │               ▼
        │     QdrantVectorStore.search(vector, filters)
        │         filters: category, equipment, difficulty, primary-muscle
        │               │
        │               ▼
        │     Top-K matching exercises returned as context
        │
        └── LLM composes the JSON plan using retrieved exercises
              as the canonical exercise list (names, categories, muscle groups)
```

---

## Phase 3 — JSON Plan Output

When all information is available and exercises have been retrieved, the agent
emits a **single structured JSON response** that mirrors the database hierarchy.
The JSON is never split across multiple messages.

### JSON Schema (Agent Output)

```json
{
  "workout_plan": {
    "training_days_per_week": 4,
    "goal": "strength_building",
    "experience_level": "intermediate",
    "workout_type": "strength",
    "plan_days": [
      {
        "day_of_week": 1,
        "workout_name": "Upper Body — Push",
        "duration_minutes": 75,
        "workout_blocks": [
          {
            "name": "Warmup",
            "order": 1,
            "exercises": [
              {
                "name": "Band Pull-Apart",
                "category": "mobility",
                "muscle_group": "shoulders",
                "equipment": "bodyweight",
                "instructions": "Hold band at shoulder width, pull apart to full extension.",
                "infos": "Activates the posterior shoulder and scapular retractors.",
                "additional_metrics": {
                  "met_value": 2.5,
                  "energy_sistem": "none",
                  "difficulty": "beginner"
                },
                "prescription": {
                  "order": 1,
                  "sets": 2,
                  "reps": 15,
                  "weight": null,
                  "duration_seconds": null,
                  "rest_seconds": 45,
                  "rpe": 3.0
                }
              }
            ]
          },
          {
            "name": "Strength",
            "order": 2,
            "exercises": [
              {
                "name": "Barbell Back Squat",
                "category": "strength",
                "muscle_group": "legs",
                "equipment": "barbell",
                "instructions": "Bar on traps, squat to parallel, drive through heels.",
                "infos": "Foundational lower body compound. Brace core and maintain neutral spine.",
                "additional_metrics": {
                  "met_value": 6.0,
                  "calories_burned_per_minute": 9.2,
                  "energy_sistem": "phosphocreatine",
                  "difficulty": "intermediate"
                },
                "prescription": {
                  "order": 1,
                  "sets": 4,
                  "reps": 5,
                  "weight": 100.0,
                  "duration_seconds": null,
                  "rest_seconds": 240,
                  "rpe": 8.5
                }
              }
            ]
          }
        ]
      }
    ]
  }
}
```

### Mapping: Agent JSON → Database Models

The agent's `exercises[].prescription` object maps to `BlockExercise`.
The agent's `exercises[]` object (minus `prescription`) maps to `Exercise`.
Extra fields in `additional_metrics` (e.g. `energy_sistem`, `difficulty`) are
stored as-is in `Exercise.additional_metrics` (JSON column, cast to `array`).

| Agent JSON field | Target model | Target column |
|---|---|---|
| `workout_plan.training_days_per_week` | `WorkoutPlan` | `training_days_per_week` |
| `workout_plan.goal` | `WorkoutPlan` | `goal` (enum `TrainingGoalType`) |
| `workout_plan.experience_level` | `WorkoutPlan` | `experience_level` (enum `ExperienceLevel`) |
| `workout_plan.workout_type` | `WorkoutPlan` | `workout_type` |
| `plan_days[].day_of_week` | `PlanDay` | `day_of_week` |
| `plan_days[].workout_name` | `PlanDay` | `workout_name` |
| `plan_days[].duration_minutes` | `PlanDay` | `duration_minutes` |
| `workout_blocks[].name` | `WorkoutBlock` | `name` |
| `workout_blocks[].order` | `WorkoutBlock` | `order` |
| `exercises[].name` | `Exercise` | resolved by name or created |
| `exercises[].category` | `Exercise` | `category` |
| `exercises[].muscle_group` | `Exercise` | `muscle_group` |
| `exercises[].equipment` | `Exercise` | `equipment` |
| `exercises[].instructions` | `Exercise` | `instructions` |
| `exercises[].infos` | `Exercise` | `infos` |
| `exercises[].additional_metrics` | `Exercise` | `additional_metrics` (JSON) |
| `exercises[].prescription.*` | `BlockExercise` | `order`, `sets`, `reps`, `weight`, `duration_seconds`, `rest_seconds`, `rpe` |

---

## Phase 4 — Async Persistence (Job)

The plan is **never persisted synchronously** inside the HTTP request.
Once the agent emits the final JSON, the controller dispatches a queued Job.

```
AgentController
        │
        ├── Detects JSON plan in agent response
        │
        └── PersistWorkoutPlanJob::dispatch(userId, planJson)
                │
                └── (async, queued worker)
                      │
                      ├── WorkoutPlanService::createFromAgentJson(User, array)
                      │     │
                      │     ├── Create WorkoutPlan
                      │     ├── foreach plan_day → Create PlanDay
                      │     │     └── foreach workout_block → Create WorkoutBlock
                      │     │           └── foreach exercise
                      │     │                 ├── Resolve or create Exercise
                      │     │                 │     (firstOrCreate by name + category)
                      │     │                 └── Create BlockExercise (prescription)
                      │     └── return WorkoutPlan (with relations loaded)
                      │
                      └── (optional) Notify user via broadcast / push
```

**Why async?**
- LLM inference may generate a plan with 50–100+ exercises.
- Each `Exercise` requires a `firstOrCreate` DB lookup.
- Nested inserts across 5 tables can exceed a typical HTTP timeout.
- A failed persistence does not abort the agent's HTTP response; it can be retried independently.

---

## Full End-to-End Flow Summary

```
[One-time]
CSV dataset ──► WorkflowCsvToQdrant ──► Qdrant vector store

[Per user session]
User message
    │
    ▼
AgentController::call()
    │
    ▼
FitnessAgent (chat loop)
    │
    ├── Missing info? ──► Ask user ──► wait for next message
    │
    └── All info collected?
            │
            ▼
        FitnessAgentRag (retrieval)
            │
            ├── Embed query (Ollama nomic-embed-text)
            └── Search Qdrant (cosine similarity + keyword filters)
                    │
                    ▼
                Top-K exercises injected as context
                    │
                    ▼
                LLM generates JSON plan
                    │
                    ▼
            AgentController returns JSON to client
                    │
                    ▼
            PersistWorkoutPlanJob::dispatch()
                    │
                    ▼ (async queue worker)
            WorkoutPlanService::createFromAgentJson()
                    │
                    ▼
            WorkoutPlan + PlanDay + WorkoutBlock
                + Exercise (firstOrCreate) + BlockExercise
                    │
                    ▼
            (optional) Notify user
```
