import { Brain } from "lucide-react";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { useWorkoutPlanGenerator } from "@/hooks/useWorkoutPlanGenerator";
import { WorkoutGeneratorHeader } from "@/components/dashboard/workout/WorkoutGeneratorHeader";
import { WorkoutProgressBar } from "@/components/dashboard/workout/WorkoutProgressBar";
import { ChatMessageList } from "@/components/dashboard/workout/ChatMessageList";
import { WorkoutStepInput } from "@/components/dashboard/workout/WorkoutStepInput";
import { PlanInfoSidebar } from "@/components/dashboard/workout/PlanInfoSidebar";
import { GoalsModal } from "@/components/dashboard/workout/GoalsModal";
import { WorkoutTypesModal } from "@/components/dashboard/workout/WorkoutTypesModal";

export function WorkoutPlanGenerator() {
    const {
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
    } = useWorkoutPlanGenerator();

    return (
        <div className="space-y-6">
            <WorkoutGeneratorHeader />

            <WorkoutProgressBar step={step} />

            <div className="grid lg:grid-cols-3 gap-6">
                {/* Chat Area */}
                <Card className="lg:col-span-2 flex flex-col">
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-2">
                                <div className="w-10 h-10 bg-gradient-to-br from-purple-600 to-indigo-600 rounded-full flex items-center justify-center">
                                    <Brain className="w-6 h-6 text-white" />
                                </div>
                                <div>
                                    <CardTitle>AI Fitness Coach</CardTitle>
                                    <CardDescription>Answer questions to build your plan</CardDescription>
                                </div>
                            </div>
                            {step > 0 && step < 7 && (
                                <Button variant="ghost" size="sm" onClick={handleReset}>
                                    Start Over
                                </Button>
                            )}
                        </div>
                    </CardHeader>
                    <CardContent className="flex flex-col flex-1 min-h-0 p-6">
                        <ChatMessageList messages={messages} isGenerating={isGenerating} />
                        <WorkoutStepInput
                            step={step}
                            planData={planData}
                            setPlanData={setPlanData}
                            handleGoalToggle={handleGoalToggle}
                            handleWorkoutTypeToggle={handleWorkoutTypeToggle}
                            handleGoals={handleGoals}
                            handleSchedule={handleSchedule}
                            handleConstraints={handleConstraints}
                            handleEquipment={handleEquipment}
                            handlePreferences={handlePreferences}
                            handleDetails={handleDetails}
                            handleReset={handleReset}
                            setShowAllGoals={setShowAllGoals}
                            setShowAllWorkoutTypes={setShowAllWorkoutTypes}
                        />
                    </CardContent>
                </Card>

                <PlanInfoSidebar planData={planData} />
            </div>

            <GoalsModal
                isOpen={showAllGoals}
                selectedGoals={planData.fitnessGoals}
                onToggle={handleGoalToggle}
                onClose={() => setShowAllGoals(false)}
            />

            <WorkoutTypesModal
                isOpen={showAllWorkoutTypes}
                selectedTypes={planData.workoutType}
                onToggle={handleWorkoutTypeToggle}
                onClose={() => setShowAllWorkoutTypes(false)}
            />
        </div>
    );
}
