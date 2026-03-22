import { useState, useRef, useEffect } from "react";
import { notify } from "@/lib/toast";
import { FITNESS_GOALS } from "@/constants/const";
import type { MessageType, WorkoutPlanData } from "@/types/workout";
import { findSuspiciousField } from "@/lib/sanitize";
import { generateWorkoutPlan, getWorkoutPlan } from "@/services/workoutPlan";
import { ApiError } from "@/lib/apiError";

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
    sports: "",
    preferredExercises: "",
    additionalNotes: "",
};

export function useWorkoutPlanGenerator() {
    const [step, setStep] = useState(0);
    const [showAllGoals, setShowAllGoals] = useState(false);
    const [messages, setMessages] = useState<MessageType[]>([INITIAL_MESSAGE]);
    const [isGenerating, setIsGenerating] = useState(false);
    const [planData, setPlanData] = useState<WorkoutPlanData>(INITIAL_PLAN_DATA);
    const [generatedPlanId, setGeneratedPlanId] = useState<number | null>(null);
    const pollingRef = useRef<ReturnType<typeof setInterval> | null>(null);

    useEffect(() => {
        return () => {
            if (pollingRef.current) {
                clearInterval(pollingRef.current);
            }
        };
    }, []);

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

    const handleGoals = () => {
        if (planData.fitnessGoals.length === 0) {
            notify.info("Please select at least one goal.");
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
            notify.info("Please select at least one available day.");
            return;
        }

        // Convert + clamp between 15 and 180
        const sessionDuration = Math.min(
            180,
            Math.max(15, Number(planData.sessionDuration))
        );

        addMessage(
            "user",
            `I can train ${planData.trainingDaysPerWeek} days per week on ${planData.availableDays.join(", ")} for ${sessionDuration} minutes per session.`
        );

        setTimeout(() => {
            addMessage(
                "assistant",
                "Perfect! Do you have any injuries or physical limitations I should be aware of? This helps me create a safe and effective plan."
            );
            setStep(2);
        }, 500);
    };

    const handleConstraints = () => {
        if (planData.injuries && findSuspiciousField({ injuries: planData.injuries })) {
            notify.error("Please describe your injuries in plain language only.");
            return;
        }

        const injuryText = planData.injuries.trim() || "No injuries or limitations";
        addMessage("user", injuryText);

        setTimeout(() => {
            addMessage("assistant", "Thanks for sharing that. What equipment do you have access to? Select all that apply.");
            setStep(3);
        }, 500);
    };

    const handleEquipment = () => {
        if (planData.equipment.length === 0) {
            notify.info("Please select at least one equipment option.");
            return;
        }

        const equipmentText = planData.equipment.join(", ");
        const gymText = planData.gymAccess ? "I have gym access" : "Home workout";
        addMessage("user", `${gymText}. Available equipment: ${equipmentText}`);

        setTimeout(() => {
            addMessage("assistant", "Almost done! Do you have any additional details to share? You can mention sports you practice, specific exercises you'd like to include, or any other requests. This step is optional.");
            setStep(4);
        }, 500);
    };

    const handleDetails = () => {
        const suspiciousField = findSuspiciousField({
            sports: planData.sports,
            preferredExercises: planData.preferredExercises,
            additionalNotes: planData.additionalNotes,
        });
        if (suspiciousField) {
            notify.error("Please use plain language to describe your fitness preferences.");
            return;
        }

        const parts: string[] = [];
        if (planData.sports.trim()) parts.push(`Sports/activities: ${planData.sports.trim()}`);
        if (planData.preferredExercises.trim()) parts.push(`Preferred exercises: ${planData.preferredExercises.trim()}`);
        if (planData.additionalNotes.trim()) parts.push(`Additional notes: ${planData.additionalNotes.trim()}`);

        const message = parts.length > 0 ? parts.join(". ") : "No additional details.";
        addMessage("user", message);

        setTimeout(() => {
            addMessage("assistant", "Excellent! I have all the information I need. Let me generate your personalized workout plan...");
            setStep(5);
            generatePlan();
        }, 500);
    };

    const generatePlan = async () => {
        setIsGenerating(true);
        setGeneratedPlanId(null);

        try {
            const pendingPlan = await generateWorkoutPlan(planData);

            pollingRef.current = setInterval(async () => {
                try {
                    const plan = await getWorkoutPlan(pendingPlan.id);

                    if (plan.status === "completed") {
                        clearInterval(pollingRef.current!);
                        pollingRef.current = null;
                        setGeneratedPlanId(plan.id);
                        addMessage("assistant",
                            "Your personalized workout plan has been generated successfully! Click below to view your complete plan."
                        );
                        setIsGenerating(false);
                        setStep(6);
                    }

                    if (plan.status === "failed") {
                        clearInterval(pollingRef.current!);
                        pollingRef.current = null;
                        addMessage("assistant",
                            "Sorry, something went wrong while generating your plan. Please try again."
                        );
                        setIsGenerating(false);
                        setStep(6);
                    }
                } catch {
                    clearInterval(pollingRef.current!);
                    pollingRef.current = null;
                    notify.error("Connection lost while checking plan status. Please try again.");
                    setIsGenerating(false);
                    setStep(4);
                }
            }, 3000);
        } catch (error) {
            setIsGenerating(false);

            if (error instanceof ApiError && error.statusCode === 403) {
                notify.error(error.message);
                addMessage("assistant", error.message);
                setStep(6);
                return;
            }

            if (error instanceof ApiError && error.statusCode === 422) {
                notify.error(error.message);
                addMessage("assistant", error.message);
                setStep(6);
                return;
            }

            if (error instanceof ApiError && error.statusCode === 429) {
                notify.error("Too many requests. Please wait a moment and try again.");
                setStep(4);
                return;
            }

            notify.error("Failed to generate workout plan. Please try again.");
            setStep(4);
        }
    };

    const handleReset = () => {
        if (pollingRef.current) {
            clearInterval(pollingRef.current);
            pollingRef.current = null;
        }
        setStep(0);
        setMessages([{ ...INITIAL_MESSAGE, timestamp: new Date() }]);
        setPlanData(INITIAL_PLAN_DATA);
        setGeneratedPlanId(null);
    };

    return {
        step,
        showAllGoals,
        setShowAllGoals,
        messages,
        isGenerating,
        planData,
        setPlanData,
        handleGoalToggle,
        handleGoals,
        handleSchedule,
        handleConstraints,
        handleEquipment,
        handleDetails,
        handleReset,
        generatedPlanId,
    };
}
