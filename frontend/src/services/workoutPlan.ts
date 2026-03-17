import { api } from "@/lib/api";
import { handleApiError } from "@/lib/handleApiError";
import type { WorkoutPlan } from "@/types/workout";

export const getWorkoutPlans = async (): Promise<WorkoutPlan[]> => {
    try {
        const response = await api.get<{ data: WorkoutPlan[] }>("/workout-plans");
        return response.data.data;
    } catch (error) {
        return handleApiError(error);
    }
};

export const getWorkoutPlan = async (id: number): Promise<WorkoutPlan> => {
    try {
        const response = await api.get<{ data: WorkoutPlan }>(`/workout-plans/${id}`);
        return response.data.data;
    } catch (error) {
        return handleApiError(error);
    }
};

export const deleteWorkoutPlan = async (id: number): Promise<void> => {
    try {
        await api.delete(`/workout-plans/${id}`);
    } catch (error) {
        return handleApiError(error);
    }
};
