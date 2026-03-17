# RAG Retrieval Strategy — Exercise Knowledge Base

## Overview

`GenerateWorkoutPlanNode` retrieves exercises from Qdrant before calling the LLM.
The retrieval quality directly determines the quality of the generated plan: if the wrong
exercises are retrieved, the agent has nothing good to work with, no matter how well the
system prompt is written.

This document explains the complete retrieval pipeline: why a single query is insufficient,
how the three-query strategy works, how results are deduplicated and capped, and how the
retrieved exercises are formatted into the prompt.

---

## Why a Single Query Is Not Enough

A naive single-query approach looks like this:

```
"intermediate male 175 80 30 fitness workout exercises"
```

This fails for two reasons:

**1. Wrong semantic signal.** Qdrant retrieves documents by cosine similarity between the
query embedding and the document embeddings. Exercise documents are indexed with content like:

```
"Exercise: Barbell Back Squat. Description: A compound lower-body movement.
 Type: strength. Level: intermediate. Equipment: barbell. Primary muscle: quadriceps."
```

The embedding model (nomic-embed-text) learns semantic proximity from language. The word
`"175"` (height in cm) or `"80"` (weight in kg) share no semantic neighbourhood with words
like `"squat"`, `"barbell"`, or `"quadriceps"`. A query built from physical stats produces
a dense vector that is semantically distant from any exercise document.

**2. Topic clustering.** Even with a better single query, all 15 retrieved documents tend
to cluster around one topic (e.g., all strength/barbell exercises) because the query has a
single semantic centre of gravity. A 4-day plan needs push, pull, legs, core, and cardio
exercises — topics that cannot all be close to the same point in the embedding space.

---

## The Three-Query Strategy

`buildRetrievalQueries()` builds three queries, each targeting a different semantic dimension.
Running them in parallel via `Concurrency::run()` and deduplicating the union gives a diverse,
high-quality candidate pool.

### Query 1 — Goal + Experience Level

**Purpose:** Find exercises that directly match what the user is trying to achieve.

```
"{goal_1} {goal_2} {experience_level} workout exercises training"

Examples:
  "muscle gain strength building intermediate workout exercises training"
  "weight loss endurance beginner workout exercises training"
  "athletic performance advanced workout exercises training"
```

**Why this works:** Goal words like `"muscle gain"`, `"strength building"`, `"weight loss"`,
`"endurance"` are present or semantically implied in exercise descriptions. Exercises indexed
for hypertrophy mention `"muscle"`, `"volume"`, `"sets"`. Cardio exercises mention `"endurance"`,
`"aerobic"`, `"heart rate"`. The experience level (`beginner`, `intermediate`, `advanced`) is
explicitly stored in the Qdrant payload and reflected in the document content.

This query has the highest recall for the user's primary objective. It is always generated.

### Query 2 — Equipment + Workout Type

**Purpose:** Constrain the retrieval to exercises the user can actually perform with their
available equipment and preferred training style.

```
"{equip_1} {equip_2} {workout_type_1} exercises"

Examples:
  "barbell dumbbells strength exercises"
  "resistance band bodyweight hiit exercises"
  "kettlebell functional conditioning exercises"
```

**Why this works:** Equipment names (`barbell`, `dumbbell`, `cable machine`, `resistance band`)
appear verbatim in exercise documents. The embedding of `"barbell squat"` is semantically very
close to a query containing `"barbell"`. Workout type words (`strength`, `hiit`, `cardio`,
`mobility`) are also indexed in the document content under the `Type` field.

This query is only generated when the user has specified equipment or workout types. It ensures
that retrieved exercises are realistically performable given the user's setup.

### Query 3 — Gym vs Bodyweight Context

**Purpose:** Fill in compound/isolation coverage (gym) or calisthenics coverage (home),
ensuring the final set has enough variety to build a multi-day split.

```
// Gym access = true
"{experience_level} gym compound isolation exercises resistance training"

// Gym access = false
"{experience_level} bodyweight calisthenics home exercises no equipment"
```

**Why this works:** Gym-based queries pull classic resistance training exercises (bench press,
deadlift, lat pulldown) that are fundamental for muscle gain and strength plans. Bodyweight
queries pull push-ups, pull-ups, planks, and mobility work, which are essential when no gym is
available. Without this third query a home user might only receive bodyweight cardio instead of
a full strength-endurance programme.

This query is always generated as a coverage safety net.

---

## Parallel Execution with `Concurrency::run()`

The three queries are executed in parallel using Laravel's `Concurrency` facade:

```php
$batchResults = Concurrency::run(
    array_map(
        fn(string $query) => fn(): array => FitnessAgentRag::make()
            ->resolveRetrieval()
            ->retrieve(new UserMessage($query)),
        $queries,
    ),
);
```

Each closure creates its own `FitnessAgentRag` instance. This is necessary because
`Concurrency::run()` uses the `process` driver by default on Windows and the `fork` driver
on Linux (requires `ext-pcntl`). Each closure is serialized and executed in an isolated
subprocess — shared object references across closures would not survive process boundaries.

**Latency impact:** Before parallelisation, three sequential Qdrant queries each taking ~400 ms
would cost ~1 200 ms total. With `Concurrency::run()`, all three complete in approximately the
time of the slowest single query (~400 ms) — a ~65% reduction in retrieval time.

Each closure internally:

1. Instantiates `FitnessAgentRag` → connects to Ollama + Qdrant
2. Calls `resolveRetrieval()` → configures pure vector-retrieval mode (no LLM synthesis)
3. Embeds the query string via `OllamaEmbeddingsProvider` (`nomic-embed-text`, 768 dimensions)
4. Sends the embedding to Qdrant, which returns the top 15 nearest documents by cosine similarity

---

## Qdrant Configuration

| Parameter      | Value  | Rationale                                                     |
| -------------- | ------ | ------------------------------------------------------------- |
| Distance       | Cosine | Normalises for vector magnitude; standard for text embeddings |
| Index          | HNSW   | Approximate nearest-neighbour; fast at scale                  |
| Dimension      | 768    | Matches nomic-embed-text output dimensionality                |
| topK per query | 15     | 3 queries × 15 = up to 45 unique candidates before dedup      |

`topK: 15` was reduced from the original `topK: 30` (single query). Because each of the three
queries targets a distinct semantic area, 15 documents per query is sufficient to cover that
area without flooding the context with redundant near-duplicates.

---

## Deduplication

Because queries overlap in semantic space, some exercises will appear in multiple result sets.
A document that matches both `"muscle gain strength intermediate"` and `"barbell gym compound"`
would appear twice without deduplication.

```php
$seen = [];
$documents = [];

foreach ($batchResults as $results) {
    foreach ($results as $document) {
        $key = md5($document->getContent());
        if (! isset($seen[$key])) {
            $seen[$key] = true;
            $documents[] = $document;
        }
    }
}
```

`md5` of the full document content is used as the deduplication key. This is intentionally
content-based rather than ID-based: if the same exercise text appears under two different Qdrant
point IDs (e.g., after a re-index), it is still treated as a duplicate and included once.

**Ordering:** Documents are added in query order (Query 1 first, then 2, then 3). Because
Query 1 targets the primary goal, its results appear first in the candidate list and are
therefore more likely to survive the final slice.

---

## Final Slice — `MAX_EXERCISES_IN_CONTEXT = 30`

After deduplication the candidate pool can contain up to 45 unique documents. The list is
sliced to the top 30 before being included in the prompt:

```php
return array_slice($documents, 0, self::MAX_EXERCISES_IN_CONTEXT);
```

**Why 30?** The LLM prompt has a 16 000 token budget. Each exercise document averages
~80–120 tokens when formatted. 30 exercises consume roughly 2 400–3 600 tokens, leaving
ample space for the system prompt, user profile, and the generated plan in the response.
Fewer exercises reduce variety; more exercises approach the token limit.

---

## Exercise Context Formatting

The retrieved documents are inserted into the prompt as a plain list:

```
=== AVAILABLE EXERCISES FROM KNOWLEDGE BASE ===
- Exercise: Barbell Back Squat. Description: … Type: strength. Level: intermediate. Equipment: barbell. Primary muscle: quadriceps.
- Exercise: Romanian Deadlift. Description: … Type: strength. Level: intermediate. Equipment: barbell. Primary muscle: hamstrings.
- Exercise: Push-Up. Description: … Type: strength. Level: beginner. Equipment: bodyweight. Primary muscle: chest.
…
```

The agent's `StepsPrompt` instructs it to select exercises **exclusively** from this list and
to use the exact name from the knowledge base in every `ExerciseData.name` field. The fallback
rule at the end of the prompt (`"Do NOT invent new exercises"`) reinforces this constraint.

---

## Full Retrieval Flow Diagram

```
GenerateWorkoutPlanNode::retrieveExercises()
        │
        ├── buildRetrievalQueries(fitnessData, fitnessGoals, equipment, preferences)
        │     │
        │     ├── Query 1: goals + experience level         always generated
        │     ├── Query 2: equipment + workout type         generated if equipment/type specified
        │     └── Query 3: gym compound OR bodyweight       always generated
        │
        ├── Concurrency::run([closure_1, closure_2, closure_3])
        │     │                │                │
        │     │   subprocess 1 │   subprocess 2 │   subprocess 3     (parallel)
        │     │                │                │
        │     │  FitnessAgentRag::make()         │
        │     │   ->resolveRetrieval()           │
        │     │   ->retrieve(UserMessage($q))    │
        │     │         │                        │
        │     │    Ollama embed (768-dim)         │
        │     │         │                        │
        │     │    Qdrant cosine search (topK=15) │
        │     │         │                        │
        │     │    Document[15]                  │
        │     │                 (same for q2, q3)│
        │     │                                  │
        │     └──── [ [15 docs], [15 docs], [15 docs] ]
        │
        ├── Deduplicate by md5(content)
        │     → up to 45 unique documents, ordered by query priority
        │
        ├── array_slice(0, 30)
        │     → Document[30]
        │
        └── formatExerciseContext(Document[30])
              → "=== AVAILABLE EXERCISES FROM KNOWLEDGE BASE ===\n- …\n- …"
```

---

## Common Failure Modes & Mitigations

| Failure                                         | Mitigation                                                                               |
| ----------------------------------------------- | ---------------------------------------------------------------------------------------- |
| Qdrant returns < 15 results for a query         | The other two queries still run; dedup pool is smaller but still valid                   |
| All queries cluster around the same exercises   | Rare with 3 semantically different queries; the agent's safety check can detect          |
| Knowledge base is empty or stale                | `formatExerciseContext` returns `''`; the agent falls back to "most appropriate from KB" |
| Ollama embedding service is down                | `FitnessAgentRag::retrieve()` throws; the job catches it → plan marked `failed`          |
| `Concurrency::run()` fork unavailable (Windows) | Laravel automatically falls back to the `process` driver; no code change needed          |
