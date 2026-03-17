import { useState, useEffect, useCallback } from "react";
import { getWorkoutPlan } from "@/services/workoutPlan";
import type { WorkoutPlan } from "@/types/workout";

export const useWorkoutPlan = (id: number) => {
    const [plan, setPlan] = useState<WorkoutPlan | null>(null);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [hasChanges, setHasChanges] = useState(false);

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
        (
            dayId: number,
            blockId: number,
            exerciseId: number,
            field: "sets" | "reps" | "weight" | "duration_seconds" | "rest_seconds" | "rpe",
            value: string,
        ) => {
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
            setHasChanges(true);
        },
        [],
    );

    return { plan, isLoading, error, hasChanges, updateExercise, refetch: fetchPlan };
};
