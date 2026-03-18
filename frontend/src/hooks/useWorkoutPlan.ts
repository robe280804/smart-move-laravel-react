import { useState, useEffect, useCallback } from "react";
import { toast } from "sonner";
import { getWorkoutPlan, updateBlockExercise } from "@/services/workoutPlan";
import { ApiError } from "@/lib/apiError";
import type { WorkoutPlan } from "@/types/workout";

export type ExerciseField = "sets" | "reps" | "weight" | "duration_seconds" | "rest_seconds" | "rpe";
export type ExerciseFieldErrors = Partial<Record<ExerciseField, string>>;

const FIELD_LIMITS: Record<ExerciseField, { min: number; max: number; label: string }> = {
    sets:             { min: 0, max: 100,  label: "Sets" },
    reps:             { min: 0, max: 100,  label: "Reps" },
    weight:           { min: 0, max: 800,  label: "Weight" },
    duration_seconds: { min: 0, max: 3600, label: "Duration" },
    rest_seconds:     { min: 0, max: 600,  label: "Rest" },
    rpe:              { min: 0, max: 10,   label: "RPE" },
};

const validateField = (field: ExerciseField, value: string): string | null => {
    if (value === "" || value === null) return null;
    const num = parseFloat(value);
    if (isNaN(num)) return `${FIELD_LIMITS[field].label} must be a number.`;
    const { min, max, label } = FIELD_LIMITS[field];
    if (num < min) return `${label} must be at least ${min}.`;
    if (num > max) return `${label} must not exceed ${max}.`;
    return null;
};

export const useWorkoutPlan = (id: number) => {
    const [plan, setPlan] = useState<WorkoutPlan | null>(null);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [hasChanges, setHasChanges] = useState(false);
    const [isSaving, setIsSaving] = useState(false);
    const [changedIds, setChangedIds] = useState<Set<number>>(new Set());
    const [fieldErrors, setFieldErrors] = useState<Record<number, ExerciseFieldErrors>>({});

    const fetchPlan = useCallback(async () => {
        setIsLoading(true);
        setError(null);
        try {
            const data = await getWorkoutPlan(id);
            setPlan(data);
        } catch {
            setError("Failed to load workout plan.");
        } finally {
            setIsLoading(false);
        }
    }, [id]);

    useEffect(() => {
        fetchPlan();
    }, [fetchPlan]);

    const updateExercise = useCallback(
        (dayId: number, blockId: number, exerciseId: number, field: ExerciseField, value: string) => {
            const validationError = validateField(field, value);

            setFieldErrors((prev) => {
                const exerciseErrors = { ...prev[exerciseId] };
                if (validationError) {
                    exerciseErrors[field] = validationError;
                } else {
                    delete exerciseErrors[field];
                }
                return { ...prev, [exerciseId]: exerciseErrors };
            });

            setPlan((prev) => {
                if (!prev) return prev;
                return {
                    ...prev,
                    plan_days: prev.plan_days.map((day) => {
                        if (day.id !== dayId) return day;
                        return {
                            ...day,
                            workout_blocks: day.workout_blocks.map((block) => {
                                if (block.id !== blockId) return block;
                                return {
                                    ...block,
                                    block_exercises: block.block_exercises.map((ex) => {
                                        if (ex.id !== exerciseId) return ex;
                                        return { ...ex, [field]: value };
                                    }),
                                };
                            }),
                        };
                    }),
                };
            });

            setChangedIds((prev) => new Set(prev).add(exerciseId));
            setHasChanges(true);
        },
        [],
    );

    const saveChanges = useCallback(async () => {
        if (!plan || changedIds.size === 0) return;

        const hasErrors = Object.values(fieldErrors).some((errs) => Object.keys(errs).length > 0);
        if (hasErrors) {
            toast.error("Please fix the highlighted errors before saving.", {
                position: "top-center",
                duration: 4000,
                style: { background: "#FF4D4F", color: "#fff" },
            });
            return;
        }

        setIsSaving(true);

        const toSave = plan.plan_days
            .flatMap((d) => d.workout_blocks)
            .flatMap((b) => b.block_exercises)
            .filter((ex) => changedIds.has(ex.id));

        const results = await Promise.allSettled(
            toSave.map((ex) =>
                updateBlockExercise(plan.id, ex.id, {
                    sets: ex.sets,
                    reps: ex.reps,
                    weight: ex.weight,
                    duration_seconds: ex.duration_seconds,
                    rest_seconds: ex.rest_seconds,
                    rpe: ex.rpe,
                }),
            ),
        );

        setIsSaving(false);

        const backendErrors: Record<number, ExerciseFieldErrors> = {};
        results.forEach((result, i) => {
            if (result.status === "rejected") {
                const err = result.reason;
                if (err instanceof ApiError && err.fieldErrors) {
                    const parsed: ExerciseFieldErrors = {};
                    for (const [field, messages] of Object.entries(err.fieldErrors)) {
                        parsed[field as ExerciseField] = (messages as string[])[0];
                    }
                    backendErrors[toSave[i].id] = parsed;
                }
            }
        });

        if (Object.keys(backendErrors).length > 0) {
            setFieldErrors((prev) => ({ ...prev, ...backendErrors }));
            toast.error("Some values are invalid. Please fix the highlighted fields.", {
                position: "top-center",
                duration: 4000,
                style: { background: "#FF4D4F", color: "#fff" },
            });

            const failedIds = new Set(Object.keys(backendErrors).map(Number));
            setChangedIds((prev) => {
                const next = new Set(prev);
                toSave.forEach((ex) => {
                    if (!failedIds.has(ex.id)) next.delete(ex.id);
                });
                return next;
            });
        } else {
            setHasChanges(false);
            setChangedIds(new Set());
            toast.success("Changes saved successfully.", {
                position: "top-center",
                duration: 3000,
                style: { background: "#22C55E", color: "#fff" },
            });
        }
    }, [plan, changedIds, fieldErrors]);

    const hasValidationErrors = Object.values(fieldErrors).some(
        (errs) => Object.keys(errs).length > 0,
    );

    return {
        plan,
        isLoading,
        error,
        hasChanges,
        isSaving,
        fieldErrors,
        hasValidationErrors,
        updateExercise,
        saveChanges,
        refetch: fetchPlan,
    };
};
