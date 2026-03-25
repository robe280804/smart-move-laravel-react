import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { AlertTriangle, RotateCcw } from "lucide-react";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import { useWorkoutPlanGenerator } from "@/hooks/useWorkoutPlanGenerator";
import { WorkoutProgressBar } from "@/components/dashboard/workout/WorkoutProgressBar";
import { WorkoutStepInput } from "@/components/dashboard/workout/WorkoutStepInput";
import { PlanInfoSidebar } from "@/components/dashboard/workout/PlanInfoSidebar";
import { GoalsModal } from "@/components/dashboard/workout/GoalsModal";
import { getFitnessInfo } from "@/services/user";
import { WORKOUT_STEPS } from "@/constants/const";

export function WorkoutPlanGenerator() {
    const [hasFitnessInfo, setHasFitnessInfo] = useState<boolean | null>(null);

    useEffect(() => {
        getFitnessInfo()
            .then((data) => setHasFitnessInfo(data !== null))
            .catch(() => setHasFitnessInfo(false));
    }, []);

    const {
        step,
        showAllGoals,
        setShowAllGoals,
        showResetConfirm,
        setShowResetConfirm,
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
    } = useWorkoutPlanGenerator();

    if (hasFitnessInfo === null) {
        return null;
    }

    if (!hasFitnessInfo) {
        return (
            <div className="flex items-center justify-center min-h-[60vh] px-4">
                <Card className="max-w-md w-full">
                    <CardContent className="flex flex-col items-center gap-4 pt-6 text-center">
                        <div className="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center">
                            <AlertTriangle className="w-6 h-6 text-amber-600" />
                        </div>
                        <CardTitle>Fitness Profile Required</CardTitle>
                        <CardDescription className="text-base">
                            Before generating a personalized workout plan, you need to complete your fitness profile
                            with your physical information (height, weight, age, etc.).
                        </CardDescription>
                        <Button asChild className="w-full bg-gradient-to-r from-blue-600 to-indigo-600">
                            <Link to="/dashboard/profile?tab=fitness">Complete Fitness Profile</Link>
                        </Button>
                    </CardContent>
                </Card>
            </div>
        );
    }

    const hasStepHeader = step < WORKOUT_STEPS.length;
    const showStartOver = step > 0 && step < WORKOUT_STEPS.length - 1;

    return (
        <div className="space-y-5 sm:space-y-6">
            <div className="animate-fade-in-up">
                <WorkoutProgressBar step={step} />
            </div>

            <div
                className="grid lg:grid-cols-3 gap-5 sm:gap-6 animate-fade-in-up"
                style={{ animationDelay: "75ms" }}
            >
                {/* Wizard Card */}
                <Card className="lg:col-span-2">
                    {hasStepHeader && (
                        <CardHeader className="pb-4">
                            <div className="flex items-start justify-between gap-4">
                                <div className="flex-1 min-w-0">
                                    <CardTitle className="text-lg sm:text-xl">
                                        {WORKOUT_STEPS[step].title}
                                    </CardTitle>
                                    <CardDescription className="mt-1">
                                        {WORKOUT_STEPS[step].description}
                                    </CardDescription>
                                </div>
                                {showStartOver && (
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => setShowResetConfirm(true)}
                                        className="flex-shrink-0 text-slate-500 hover:text-slate-700"
                                    >
                                        <RotateCcw className="w-3.5 h-3.5 mr-1.5" />
                                        <span className="hidden sm:inline">Start Over</span>
                                    </Button>
                                )}
                            </div>
                        </CardHeader>
                    )}
                    <CardContent className={hasStepHeader ? "pt-0" : undefined}>
                        <WorkoutStepInput
                            step={step}
                            planData={planData}
                            setPlanData={setPlanData}
                            handleGoalToggle={handleGoalToggle}
                            handleBack={handleBack}
                            handleGoals={handleGoals}
                            handleSchedule={handleSchedule}
                            handleEquipment={handleEquipment}
                            handleDetails={handleDetails}
                            handleReset={handleReset}
                            setShowAllGoals={setShowAllGoals}
                            generatedPlanId={generatedPlanId}
                            generationFailed={generationFailed}
                            failureReason={failureReason}
                        />
                    </CardContent>
                </Card>

                {/* Summary Sidebar */}
                <PlanInfoSidebar planData={planData} />
            </div>

            <GoalsModal
                isOpen={showAllGoals}
                selectedGoal={planData.fitnessGoals}
                onToggle={handleGoalToggle}
                onClose={() => setShowAllGoals(false)}
            />

            {/* Reset confirmation dialog */}
            <Dialog open={showResetConfirm} onOpenChange={setShowResetConfirm}>
                <DialogContent showCloseButton={false} className="sm:max-w-sm">
                    <DialogHeader>
                        <DialogTitle>Start over?</DialogTitle>
                        <DialogDescription>
                            All your current progress will be lost. This cannot be undone.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setShowResetConfirm(false)}>
                            Cancel
                        </Button>
                        <Button
                            variant="destructive"
                            onClick={handleReset}
                        >
                            Start Over
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    );
}
