export type MessageType = {
    id: string;
    role: "user" | "assistant";
    content: string;
    timestamp: Date;
};

// ─── API response types ───────────────────────────────────────────────────────

export type AdditionalMetrics = {
    description?: string | null;
    met_value?: number | null;
    energy_system?: string | null;
    difficulty?: string | null;
};

export type Exercise = {
    id: number;
    name: string | null;
    category: string;
    muscle_group: string | null;
    equipment: string | null;
    instructions: string | null;
    infos: string | null;
    additional_metrics: AdditionalMetrics | null;
    created_at: string;
    updated_at: string;
};

export type BlockExercise = {
    id: number;
    workout_block_id: number;
    exercise_id: number;
    order: number | null;
    sets: number | null;
    reps: number | null;
    weight: string | null;       // decimal cast → serialised as string
    duration_seconds: number | null;
    rest_seconds: number | null;
    rpe: string | null;          // decimal cast → serialised as string
    exercise: Exercise;
    created_at: string;
    updated_at: string;
};

export type WorkoutBlock = {
    id: number;
    plan_day_id: number;
    name: string;
    order: number;
    block_exercises: BlockExercise[];
    created_at: string;
    updated_at: string;
};

export type PlanDay = {
    id: number;
    workout_plan_id: number;
    day_of_week: number;          // 1 = Monday … 7 = Sunday
    workout_name: string | null;
    duration_minutes: number;
    workout_blocks: WorkoutBlock[];
    created_at: string;
    updated_at: string;
};

export type GenerationRequest = {
    fitness_goals: string;
    schedule: {
        training_days_per_week: number;
        available_days: string[];
        session_duration: number;
    };
    equipment: {
        items: string[];
        gym_access: boolean;
    };
    constraints: string | null;
    preferences: {
        workout_types: string[];
        sports: string | null;
        preferred_exercises: string | null;
        additional_notes: string | null;
    };
};

export type WorkoutPlan = {
    id: number;
    user_id: number;
    status: "pending" | "processing" | "completed" | "failed";
    training_days_per_week: number;
    goal: string;
    experience_level: string;
    workout_type: string;
    generation_request: GenerationRequest | null;
    failure_reason: string | null;
    plan_days: PlanDay[];
    created_at: string;
    updated_at: string;
};

// ─── Form data types ──────────────────────────────────────────────────────────

export type WorkoutPlanData = {
    fitnessGoals: string;
    trainingDaysPerWeek: number;
    availableDays: string[];
    sessionDuration: number|string;   // string for the form
    restDays: number;
    injuries: string;
    equipment: string[];
    gymAccess: boolean;
    sports: string;
    preferredExercises: string;
    additionalNotes: string;
};
