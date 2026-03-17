import { useState, useEffect, useCallback } from "react";
import { getWorkoutPlans, deleteWorkoutPlan } from "@/services/workoutPlan";
import type { WorkoutPlan } from "@/types/workout";

export const useWorkoutPlans = () => {
    const [plans, setPlans] = useState<WorkoutPlan[]>([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    const fetchPlans = useCallback(async () => {
        setIsLoading(true);
        setError(null);
        try {
            const data = await getWorkoutPlans();
            setPlans(data);
        } catch {
            setError("Failed to load workout plans.");
        } finally {
            setIsLoading(false);
        }
    }, []);

    useEffect(() => {
        fetchPlans();
    }, [fetchPlans]);

    const deletePlan = useCallback(async (id: number) => {
        await deleteWorkoutPlan(id);
        setPlans((prev) => prev.filter((p) => p.id !== id));
    }, []);

    return { plans, isLoading, error, deletePlan, refetch: fetchPlans };
};
