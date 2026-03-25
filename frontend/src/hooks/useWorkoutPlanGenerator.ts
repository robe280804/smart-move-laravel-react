import { useState, useRef, useEffect } from "react";
import { notify } from "@/lib/toast";
import type { WorkoutPlanData } from "@/types/workout";
import { findSuspiciousField } from "@/lib/sanitize";
import { generateWorkoutPlan, getWorkoutPlan } from "@/services/workoutPlan";
import { trackGeneratingPlan, updateGeneratingPlanStatus } from "@/hooks/useGeneratingPlans";
import { ApiError } from "@/lib/apiError";

const INITIAL_PLAN_DATA: WorkoutPlanData = {
    fitnessGoals: "",
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

// Step map:
//  0 → Goal
//  1 → Schedule
//  2 → Equipment
//  3 → Preferences (injuries + optional details)
//  4 → Generating (auto)
//  5 → Done

export type GenerationFailureReason = "email_not_verified" | "plan_limit" | "generic";

export function useWorkoutPlanGenerator() {
    const [step, setStep] = useState(0);
    const [showAllGoals, setShowAllGoals] = useState(false);
    const [showResetConfirm, setShowResetConfirm] = useState(false);
    const [isGenerating, setIsGenerating] = useState(false);
    const [generationFailed, setGenerationFailed] = useState(false);
    const [failureReason, setFailureReason] = useState<GenerationFailureReason | null>(null);
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

    const handleGoalToggle = (goal: string) => {
        setPlanData(prev => ({
            ...prev,
            fitnessGoals: prev.fitnessGoals === goal ? "" : goal,
        }));
    };

    const handleBack = () => {
        setStep(prev => Math.max(0, prev - 1));
    };

    const handleGoals = () => {
        if (!planData.fitnessGoals) {
            notify.info("Please select a fitness goal.");
            return;
        }
        setStep(1);
    };

    const handleSchedule = () => {
        if (planData.availableDays.length === 0) {
            notify.info("Please select at least one available day.");
            return;
        }
        const sessionDuration = Math.min(180, Math.max(15, Number(planData.sessionDuration)));
        setPlanData(prev => ({ ...prev, sessionDuration }));
        setStep(2);
    };

    const handleEquipment = () => {
        if (planData.equipment.length === 0) {
            notify.info("Please select a training setup and at least one equipment option.");
            return;
        }
        setStep(3);
    };

    const handleDetails = () => {
        const suspiciousField = findSuspiciousField({
            injuries: planData.injuries,
            sports: planData.sports,
            preferredExercises: planData.preferredExercises,
            additionalNotes: planData.additionalNotes,
        });
        if (suspiciousField) {
            notify.error("Please use plain language to describe your preferences.");
            return;
        }
        setStep(4);
        generatePlan();
    };

    const generatePlan = async () => {
        setIsGenerating(true);
        setGenerationFailed(false);
        setFailureReason(null);
        setGeneratedPlanId(null);

        try {
            const pendingPlan = await generateWorkoutPlan(planData);
            trackGeneratingPlan(pendingPlan);

            pollingRef.current = setInterval(async () => {
                try {
                    const plan = await getWorkoutPlan(pendingPlan.id);

                    if (plan.status === "completed") {
                        clearInterval(pollingRef.current!);
                        pollingRef.current = null;
                        updateGeneratingPlanStatus(pendingPlan.id, "completed");
                        setGeneratedPlanId(plan.id);
                        setIsGenerating(false);
                        setStep(5);
                    }

                    if (plan.status === "failed") {
                        clearInterval(pollingRef.current!);
                        pollingRef.current = null;
                        updateGeneratingPlanStatus(pendingPlan.id, "failed");
                        setIsGenerating(false);
                        setGenerationFailed(true);
                        setStep(5);
                    }
                } catch {
                    clearInterval(pollingRef.current!);
                    pollingRef.current = null;
                    notify.error("Connection lost while checking plan status. Please try again.");
                    setIsGenerating(false);
                    setStep(3);
                }
            }, 3000);
        } catch (error) {
            setIsGenerating(false);

            if (error instanceof ApiError && error.statusCode === 403) {
                notify.error(error.message);
                const msg = error.message.toLowerCase();
                if (msg.includes("verif")) {
                    setFailureReason("email_not_verified");
                } else if (msg.includes("limit") || msg.includes("generation") || msg.includes("active plan")) {
                    setFailureReason("plan_limit");
                } else {
                    setFailureReason("generic");
                }
                setGenerationFailed(true);
                setStep(5);
                return;
            }

            if (error instanceof ApiError && error.statusCode === 422) {
                notify.error(error.message);
                setGenerationFailed(true);
                setFailureReason("generic");
                setStep(5);
                return;
            }

            if (error instanceof ApiError && error.statusCode === 429) {
                notify.error("Too many requests. Please wait a moment and try again.");
                setStep(3);
                return;
            }

            notify.error("Failed to generate workout plan. Please try again.");
            setStep(3);
        }
    };

    const handleReset = () => {
        if (pollingRef.current) {
            clearInterval(pollingRef.current);
            pollingRef.current = null;
        }
        setStep(0);
        setPlanData(INITIAL_PLAN_DATA);
        setGeneratedPlanId(null);
        setGenerationFailed(false);
        setFailureReason(null);
        setShowResetConfirm(false);
        setIsGenerating(false);
    };

    return {
        step,
        showAllGoals,
        setShowAllGoals,
        showResetConfirm,
        setShowResetConfirm,
        isGenerating,
        generationFailed,
        failureReason,
        planData,
        setPlanData,
        handleGoalToggle,
        handleBack,
        handleGoals,
        handleSchedule,
        handleEquipment,
        handleDetails,
        handleReset,
        generatedPlanId,
    };
}
