import { api } from "@/lib/api";
import { handleApiError } from "@/lib/handleApiError";
import type { BlockExercise, WorkoutPlan, WorkoutPlanData } from "@/types/workout";
import { GOAL_TO_WORKOUT_TYPES } from "@/constants/const";

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

type UpdateBlockExerciseData = Partial<
    Pick<BlockExercise, "sets" | "reps" | "weight" | "duration_seconds" | "rest_seconds" | "rpe">
>;

const deriveWorkoutTypes = (goal: string): string[] => {
    return (GOAL_TO_WORKOUT_TYPES[goal] ?? []).slice(0, 3);
};

export const generateWorkoutPlan = async (data: WorkoutPlanData): Promise<WorkoutPlan> => {
    try {
        const response = await api.post<{ data: WorkoutPlan }>("/agent/generate-workout", {
            fitness_goals: data.fitnessGoals,
            training_days_per_week: data.trainingDaysPerWeek,
            available_days: data.availableDays,
            session_duration: Number(data.sessionDuration),
            injuries: data.injuries || null,
            equipment: data.equipment,
            gym_access: data.gymAccess,
            workout_type: deriveWorkoutTypes(data.fitnessGoals),  // single goal string
            sports: data.sports || null,
            preferred_exercises: data.preferredExercises || null,
            additional_notes: data.additionalNotes || null,
        });
        return response.data.data;
    } catch (error) {
        return handleApiError(error);
    }
};

export const downloadWorkoutPlanPdf = async (planId: number): Promise<void> => {
    try {
        const response = await api.get(`/workout-plans/${planId}/pdf`, {
            responseType: "blob",
        });

        const blob = new Blob([response.data], { type: "application/pdf" });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.href = url;
        link.download = `workout-plan-${planId}.pdf`;
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    } catch (error) {
        return handleApiError(error);
    }
};

export const updateBlockExercise = async (
    planId: number,
    blockExerciseId: number,
    data: UpdateBlockExerciseData,
): Promise<BlockExercise> => {
    try {
        const response = await api.patch<{ data: BlockExercise }>(
            `/workout-plans/${planId}/exercises/${blockExerciseId}`,
            data,
        );
        return response.data.data;
    } catch (error) {
        return handleApiError(error);
    }
};
