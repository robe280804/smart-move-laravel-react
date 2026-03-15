import { Brain } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { FITNESS_GOALS, WORKOUT_TYPES } from "@/constants/const";
import type { WorkoutPlanData } from "@/types/workout";

interface PlanInfoSidebarProps {
    planData: WorkoutPlanData;
}

export function PlanInfoSidebar({ planData }: PlanInfoSidebarProps) {
    return (
        <div className="space-y-4">
            <Card>
                <CardHeader>
                    <CardTitle className="text-lg">Your Information</CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                    {planData.fitnessGoals.length > 0 && (
                        <div>
                            <p className="text-sm text-slate-600 mb-1">Fitness Goals</p>
                            <div className="flex flex-wrap gap-1">
                                {planData.fitnessGoals.map(g => (
                                    <Badge key={g} className="bg-blue-600">
                                        {FITNESS_GOALS.find(fg => fg.value === g)?.label}
                                    </Badge>
                                ))}
                            </div>
                        </div>
                    )}
                    {planData.availableDays.length > 0 && (
                        <div>
                            <p className="text-sm text-slate-600 mb-1">Schedule</p>
                            <p className="text-sm font-medium text-slate-900">
                                {planData.trainingDaysPerWeek} days/week • {planData.sessionDuration} min
                            </p>
                            <p className="text-xs text-slate-600 mt-1">
                                {planData.availableDays.slice(0, 3).map(d => d.slice(0, 3)).join(", ")}
                                {planData.availableDays.length > 3 && ` +${planData.availableDays.length - 3}`}
                            </p>
                        </div>
                    )}
                    {planData.injuries && (
                        <div>
                            <p className="text-sm text-slate-600 mb-1">Injuries / Limitations</p>
                            <p className="text-xs font-medium text-slate-900">Provided</p>
                        </div>
                    )}
                    {planData.equipment.length > 0 && (
                        <div>
                            <p className="text-sm text-slate-600 mb-1">Equipment</p>
                            <p className="text-sm font-medium text-slate-900">
                                {planData.equipment.length} selected
                            </p>
                        </div>
                    )}
                    {planData.workoutType.length > 0 && (
                        <div>
                            <p className="text-sm text-slate-600 mb-1">Workout Types</p>
                            <div className="flex flex-wrap gap-1">
                                {planData.workoutType.map(type => (
                                    <Badge key={type} variant="outline" className="text-xs">
                                        {WORKOUT_TYPES.find(t => t.value === type)?.label}
                                    </Badge>
                                ))}
                            </div>
                        </div>
                    )}
                    {(planData.sports || planData.preferredExercises || planData.additionalNotes) && (
                        <div>
                            <p className="text-sm text-slate-600 mb-1">Additional Details</p>
                            <div className="space-y-1">
                                {planData.sports && <p className="text-xs  font-medium text-slate-900">Sports / Activities: Provided</p>}
                                {planData.preferredExercises && <p className="text-xs font-medium text-slate-900">Exercises: Provided</p>}
                                {planData.additionalNotes && <p className="text-xs font-medium text-slate-900">Notes: Provided</p>}
                            </div>
                        </div>
                    )}
                </CardContent>
            </Card>

            <Card className="bg-gradient-to-br from-purple-50 to-blue-50 border-purple-200">
                <CardContent className="p-4 space-y-2">
                    <div className="flex items-center gap-2">
                        <Brain className="w-5 h-5 text-purple-600" />
                        <p className="font-semibold text-slate-900">AI-Powered</p>
                    </div>
                    <p className="text-sm text-slate-700">
                        Our AI analyzes your inputs to create a scientifically-backed, personalized workout plan optimized for your specific goals and constraints.
                    </p>
                </CardContent>
            </Card>
        </div>
    );
}
