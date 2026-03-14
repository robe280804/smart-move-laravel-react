export type MessageType = {
    id: string;
    role: "user" | "assistant";
    content: string;
    timestamp: Date;
};

export type WorkoutPlanData = {
    fitnessGoals: string[];
    trainingDaysPerWeek: number;
    availableDays: string[];
    sessionDuration: number;
    restDays: number;
    injuries: string;
    equipment: string[];
    gymAccess: boolean;
    workoutType: string[];
};
