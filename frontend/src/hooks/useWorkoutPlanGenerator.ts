import { useState } from "react";
import { toast } from "sonner";
import { FITNESS_GOALS, WORKOUT_TYPES } from "@/constants/const";
import type { MessageType, WorkoutPlanData } from "@/types/workout";

const INITIAL_MESSAGE: MessageType = {
    id: "1",
    role: "assistant",
    content: "Hello! I'm your AI fitness coach. I'll help you create a personalized workout plan tailored to your goals, schedule, and preferences. Let's get started! 🎯",
    timestamp: new Date()
};

const INITIAL_PLAN_DATA: WorkoutPlanData = {
    fitnessGoals: [],
    trainingDaysPerWeek: 3,
    availableDays: [],
    sessionDuration: 60,
    restDays: 2,
    injuries: "",
    equipment: [],
    gymAccess: false,
    workoutType: [],
    sports: "",
    preferredExercises: "",
    additionalNotes: "",
};

export function useWorkoutPlanGenerator() {
    const [step, setStep] = useState(0);
    const [showAllGoals, setShowAllGoals] = useState(false);
    const [showAllWorkoutTypes, setShowAllWorkoutTypes] = useState(false);
    const [messages, setMessages] = useState<MessageType[]>([INITIAL_MESSAGE]);
    const [isGenerating, setIsGenerating] = useState(false);
    const [planData, setPlanData] = useState<WorkoutPlanData>(INITIAL_PLAN_DATA);

    const addMessage = (role: "user" | "assistant", content: string) => {
        const newMessage: MessageType = {
            id: Date.now().toString(),
            role,
            content,
            timestamp: new Date()
        };
        setMessages(prev => [...prev, newMessage]);
    };

    const handleGoalToggle = (goal: string) => {
        const isSelected = planData.fitnessGoals.includes(goal);
        if (isSelected) {
            setPlanData({ ...planData, fitnessGoals: planData.fitnessGoals.filter(g => g !== goal) });
        } else {
            if (planData.fitnessGoals.length >= 3) return;
            setPlanData({ ...planData, fitnessGoals: [...planData.fitnessGoals, goal] });
        }
    };

    const handleWorkoutTypeToggle = (type: string) => {
        const isSelected = planData.workoutType.includes(type);
        if (isSelected) {
            setPlanData({ ...planData, workoutType: planData.workoutType.filter(t => t !== type) });
        } else {
            if (planData.workoutType.length >= 3) return;
            setPlanData({ ...planData, workoutType: [...planData.workoutType, type] });
        }
    };

    const handleGoals = () => {
        if (planData.fitnessGoals.length === 0) {
            toast.info("Please select at least one goal", {
                position: "top-center",
                duration: 5000,
                style: { background: "#3B82F6", color: "#fff" },
            });
            return;
        }

        const goalLabels = planData.fitnessGoals
            .map(g => FITNESS_GOALS.find(fg => fg.value === g)?.label)
            .join(", ");
        addMessage("user", `My goals: ${goalLabels}`);

        setTimeout(() => {
            addMessage("assistant", "Great choice! Now, let's talk about your schedule. How many days per week can you commit to training?");
            setStep(1);
        }, 500);
    };

    const handleSchedule = () => {
        if (planData.availableDays.length === 0) {
            toast.info("Please select at least one available day", {
                position: "top-center",
                duration: 5000,
                style: { background: "#3B82F6", color: "#fff" },
            });
            return;
        }

        addMessage("user", `I can train ${planData.trainingDaysPerWeek} days per week on ${planData.availableDays.join(", ")} for ${planData.sessionDuration} minutes per session.`);

        setTimeout(() => {
            addMessage("assistant", "Perfect! Do you have any injuries or physical limitations I should be aware of? This helps me create a safe and effective plan.");
            setStep(2);
        }, 500);
    };

    const handleConstraints = () => {
        const injuryText = planData.injuries.trim() || "No injuries or limitations";
        addMessage("user", injuryText);

        setTimeout(() => {
            addMessage("assistant", "Thanks for sharing that. What equipment do you have access to? Select all that apply.");
            setStep(3);
        }, 500);
    };

    const handleEquipment = () => {
        if (planData.equipment.length === 0) {
            toast.info("Please select at least one equipment option", {
                position: "top-center",
                duration: 5000,
                style: { background: "#3B82F6", color: "#fff" },
            });
            return;
        }

        const equipmentText = planData.equipment.join(", ");
        const gymText = planData.gymAccess ? "I have gym access" : "Home workout";
        addMessage("user", `${gymText}. Available equipment: ${equipmentText}`);

        setTimeout(() => {
            addMessage("assistant", "Almost done! What type of workouts do you prefer? You can select multiple options.");
            setStep(4);
        }, 500);
    };

    const handlePreferences = () => {
        if (planData.workoutType.length === 0) {
            toast.info("Please select at least one workout type", {
                position: "top-center",
                duration: 5000,
                style: { background: "#3B82F6", color: "#fff" },
            });
            return;
        }

        const typeLabels = planData.workoutType
            .map(type => WORKOUT_TYPES.find(t => t.value === type)?.label)
            .join(", ");
        addMessage("user", `I prefer: ${typeLabels}`);

        setTimeout(() => {
            addMessage("assistant", "Almost there! Do you have any additional details to share? You can mention sports you practice, specific exercises you'd like to include, or any other requests. This step is optional.");
            setStep(5);
        }, 500);
    };

    const handleDetails = () => {
        const parts: string[] = [];
        if (planData.sports.trim()) parts.push(`Sports/activities: ${planData.sports.trim()}`);
        if (planData.preferredExercises.trim()) parts.push(`Preferred exercises: ${planData.preferredExercises.trim()}`);
        if (planData.additionalNotes.trim()) parts.push(`Additional notes: ${planData.additionalNotes.trim()}`);

        const message = parts.length > 0 ? parts.join(". ") : "No additional details.";
        addMessage("user", message);

        setTimeout(() => {
            addMessage("assistant", "Excellent! I have all the information I need. Let me generate your personalized workout plan...");
            setStep(6);
            generatePlan();
        }, 500);
    };

    const generatePlan = () => {
        setIsGenerating(true);

        setTimeout(() => {
            const goalLabels = planData.fitnessGoals
                .map(g => FITNESS_GOALS.find(fg => fg.value === g)?.label)
                .join(", ");
            const planSummary = `
                🎯 **Your Personalized ${planData.trainingDaysPerWeek}-Day Workout Plan**

                **Goal${planData.fitnessGoals.length > 1 ? "s" : ""}:** ${goalLabels}
                **Schedule:** ${planData.availableDays.join(", ")} • ${planData.sessionDuration} min/session
                **Equipment:** ${planData.equipment.join(", ")}
                **Focus:** ${planData.workoutType.map(t => WORKOUT_TYPES.find(wt => wt.value === t)?.label).join(", ")}

                Your plan has been generated successfully! It includes:
                ✅ Progressive overload structure
                ✅ Personalized exercise selection
                ✅ Recovery optimization
                ✅ AI-adjusted intensity levels

                Would you like to view your complete workout plan or save it to your dashboard?
            `.trim();

            addMessage("assistant", planSummary);
            setIsGenerating(false);
            setStep(7);
        }, 3000);
    };

    const handleReset = () => {
        setStep(0);
        setMessages([{ ...INITIAL_MESSAGE, timestamp: new Date() }]);
        setPlanData(INITIAL_PLAN_DATA);
    };

    return {
        step,
        showAllGoals,
        setShowAllGoals,
        showAllWorkoutTypes,
        setShowAllWorkoutTypes,
        messages,
        isGenerating,
        planData,
        setPlanData,
        handleGoalToggle,
        handleWorkoutTypeToggle,
        handleGoals,
        handleSchedule,
        handleConstraints,
        handleEquipment,
        handlePreferences,
        handleDetails,
        handleReset,
    };
}
