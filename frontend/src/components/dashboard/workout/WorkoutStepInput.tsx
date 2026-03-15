import { ArrowRight, Check, ChevronDown, ChevronRight, Info, Sparkles } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Checkbox } from "@/components/ui/checkbox";
import { DAYS_OF_WEEK, EQUIPMENT_OPTIONS, FITNESS_GOALS, WORKOUT_TYPES } from "@/constants/const";
import type { WorkoutPlanData } from "@/types/workout";

interface WorkoutStepInputProps {
    step: number;
    planData: WorkoutPlanData;
    setPlanData: (data: WorkoutPlanData) => void;
    handleGoalToggle: (goal: string) => void;
    handleWorkoutTypeToggle: (type: string) => void;
    handleGoals: () => void;
    handleSchedule: () => void;
    handleConstraints: () => void;
    handleEquipment: () => void;
    handlePreferences: () => void;
    handleDetails: () => void;
    handleReset: () => void;
    setShowAllGoals: (show: boolean) => void;
    setShowAllWorkoutTypes: (show: boolean) => void;
}

export function WorkoutStepInput({
    step,
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
    setShowAllGoals,
    setShowAllWorkoutTypes,
}: WorkoutStepInputProps) {
    if (step === 0) {
        return (
            <div className="space-y-3">
                <div className="flex items-center justify-between">
                    <Label>What are your fitness goals? (select 1–3)</Label>
                    {planData.fitnessGoals.length > 0 && (
                        <span className="text-xs text-blue-600 font-medium">{planData.fitnessGoals.length}/3 selected</span>
                    )}
                </div>
                <div className="grid md:grid-cols-2 gap-3">
                    {FITNESS_GOALS.slice(0, 4).map((goal) => {
                        const isSelected = planData.fitnessGoals.includes(goal.value);
                        return (
                            <button
                                key={goal.value}
                                onClick={() => handleGoalToggle(goal.value)}
                                className={`p-4 border-2 rounded-xl transition-all text-left ${isSelected
                                    ? "border-blue-600 bg-blue-50"
                                    : planData.fitnessGoals.length >= 3
                                        ? "border-slate-200 opacity-50 cursor-not-allowed"
                                        : "border-slate-200 hover:border-blue-600 hover:bg-blue-50"
                                    }`}
                            >
                                <div className="flex items-start gap-3">
                                    <span className="text-2xl">{goal.icon}</span>
                                    <div className="flex-1">
                                        <p className={`font-semibold ${isSelected ? "text-blue-600" : "text-slate-900"}`}>
                                            {goal.label}
                                        </p>
                                        <p className="text-sm text-slate-600">{goal.description}</p>
                                    </div>
                                    {isSelected && <Check className="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />}
                                </div>
                            </button>
                        );
                    })}
                </div>
                <Button variant="outline" className="w-full" onClick={() => setShowAllGoals(true)}>
                    View all goals <ChevronDown className="w-4 h-4 ml-2" />
                </Button>
                <Button
                    onClick={handleGoals}
                    className="w-full bg-gradient-to-r from-blue-600 to-indigo-600"
                    disabled={planData.fitnessGoals.length === 0}
                >
                    Continue <ArrowRight className="w-4 h-4 ml-2" />
                </Button>
            </div>
        );
    }

    if (step === 1) {
        return (
            <div className="space-y-4">
                <div className="grid md:grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <Label htmlFor="trainingDays">Training days per week</Label>
                        <Input
                            id="trainingDays"
                            type="number"
                            min="1"
                            max="7"
                            value={planData.trainingDaysPerWeek}
                            onChange={(e) => {
                                const val = Math.max(1, Math.min(7, parseInt(e.target.value) || 1));
                                setPlanData({ ...planData, trainingDaysPerWeek: val });
                            }}
                        />
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor="sessionDuration">Session duration (minutes)</Label>
                        <Input
                            id="sessionDuration"
                            type="number"
                            min="15"
                            max="180"
                            value={planData.sessionDuration}
                            onChange={(e) => {
                                const val = Math.max(15, Math.min(180, parseInt(e.target.value) || 15));
                                setPlanData({ ...planData, sessionDuration: val });
                            }}
                        />
                    </div>
                </div>
                <div className="space-y-2">
                    <Label>Available days of the week</Label>
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-2">
                        {DAYS_OF_WEEK.map((day) => (
                            <label
                                key={day}
                                className={`flex items-center gap-2 p-3 border-2 rounded-lg cursor-pointer transition-all ${planData.availableDays.includes(day)
                                    ? "border-blue-600 bg-blue-50"
                                    : "border-slate-200 hover:border-slate-300"
                                    }`}
                            >
                                <Checkbox
                                    checked={planData.availableDays.includes(day)}
                                    onCheckedChange={(checked) => {
                                        if (checked) {
                                            setPlanData({ ...planData, availableDays: [...planData.availableDays, day] });
                                        } else {
                                            setPlanData({ ...planData, availableDays: planData.availableDays.filter(d => d !== day) });
                                        }
                                    }}
                                />
                                <span className="text-sm font-medium">{day.slice(0, 3)}</span>
                            </label>
                        ))}
                    </div>
                </div>
                <Button onClick={handleSchedule} className="w-full bg-gradient-to-r from-blue-600 to-indigo-600">
                    Continue <ArrowRight className="w-4 h-4 ml-2" />
                </Button>
            </div>
        );
    }

    if (step === 2) {
        return (
            <div className="space-y-4">
                <div className="space-y-2">
                    <div className="flex items-center justify-between">
                        <Label htmlFor="injuries">Injuries or limitations (optional)</Label>
                        <span className={`text-xs ${planData.injuries.length >= 200 ? "text-red-500" : "text-slate-400"}`}>
                            {planData.injuries.length}/200
                        </span>
                    </div>
                    <Input
                        id="injuries"
                        placeholder="e.g., Lower back pain, knee injury, etc."
                        value={planData.injuries}
                        onChange={(e) => setPlanData({ ...planData, injuries: e.target.value })}
                        maxLength={200}
                    />
                    <p className="text-xs text-slate-500">Leave blank if none</p>
                </div>
                <Button onClick={handleConstraints} className="w-full bg-gradient-to-r from-blue-600 to-indigo-600">
                    Continue <ArrowRight className="w-4 h-4 ml-2" />
                </Button>
            </div>
        );
    }

    if (step === 3) {
        return (
            <div className="space-y-4">
                <div className="flex items-center gap-2 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <Checkbox
                        id="gymAccess"
                        checked={planData.gymAccess}
                        onCheckedChange={(checked) => setPlanData({ ...planData, gymAccess: checked as boolean })}
                    />
                    <Label htmlFor="gymAccess" className="cursor-pointer">I have gym access</Label>
                </div>
                <div className="space-y-2">
                    <Label>Available equipment (select all that apply)</Label>
                    <div className="grid md:grid-cols-2 gap-2">
                        {EQUIPMENT_OPTIONS.map((equipment) => (
                            <label
                                key={equipment}
                                className={`flex items-center gap-2 p-3 border-2 rounded-lg cursor-pointer transition-all ${planData.equipment.includes(equipment)
                                    ? "border-blue-600 bg-blue-50"
                                    : "border-slate-200 hover:border-slate-300"
                                    }`}
                            >
                                <Checkbox
                                    checked={planData.equipment.includes(equipment)}
                                    onCheckedChange={(checked) => {
                                        if (checked) {
                                            setPlanData({ ...planData, equipment: [...planData.equipment, equipment] });
                                        } else {
                                            setPlanData({ ...planData, equipment: planData.equipment.filter(e => e !== equipment) });
                                        }
                                    }}
                                />
                                <span className="text-sm font-medium">{equipment}</span>
                            </label>
                        ))}
                    </div>
                </div>
                <Button onClick={handleEquipment} className="w-full bg-gradient-to-r from-blue-600 to-indigo-600">
                    Continue <ArrowRight className="w-4 h-4 ml-2" />
                </Button>
            </div>
        );
    }

    if (step === 4) {
        return (
            <div className="space-y-4">
                <div className="space-y-2">
                    <div className="flex items-center justify-between">
                        <Label>Preferred workout types (select 1–3)</Label>
                        {planData.workoutType.length > 0 && (
                            <span className="text-xs text-blue-600 font-medium">{planData.workoutType.length}/3 selected</span>
                        )}
                    </div>
                    <div className="grid md:grid-cols-2 gap-3">
                        {WORKOUT_TYPES.slice(0, 4).map((type) => {
                            const isSelected = planData.workoutType.includes(type.value);
                            return (
                                <button
                                    key={type.value}
                                    onClick={() => handleWorkoutTypeToggle(type.value)}
                                    className={`p-4 border-2 rounded-xl transition-all text-left ${isSelected
                                        ? "border-blue-600 bg-blue-50"
                                        : planData.workoutType.length >= 3
                                            ? "border-slate-200 opacity-50 cursor-not-allowed"
                                            : "border-slate-200 hover:border-blue-600 hover:bg-blue-50"
                                        }`}
                                >
                                    <div className="flex items-center gap-3">
                                        <span className="text-2xl">{type.icon}</span>
                                        <span className={`font-medium flex-1 ${isSelected ? "text-blue-600" : "text-slate-900"}`}>
                                            {type.label}
                                        </span>
                                        {isSelected && <Check className="w-5 h-5 text-blue-600 flex-shrink-0" />}
                                    </div>
                                </button>
                            );
                        })}
                    </div>
                </div>
                <Button variant="outline" className="w-full" onClick={() => setShowAllWorkoutTypes(true)}>
                    View all workout types <ChevronDown className="w-4 h-4 ml-2" />
                </Button>
                <Button
                    onClick={handlePreferences}
                    className="w-full bg-gradient-to-r from-blue-600 to-indigo-600"
                    disabled={planData.workoutType.length === 0}
                >
                    Continue <ArrowRight className="w-4 h-4 ml-2" />
                </Button>
            </div>
        );
    }

    if (step === 5) {
        return (
            <div className="space-y-4">
                <div className="flex gap-3 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                    <Info className="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />
                    <div>
                        <p className="text-sm font-semibold text-blue-900">Add more details (optional)</p>
                        <p className="text-sm text-blue-700 mt-0.5">
                            The more context you provide, the more tailored your plan will be. Feel free to skip any fields you don't have answers for.
                        </p>
                    </div>
                </div>
                <div className="space-y-2">
                    <div className="flex items-center justify-between">
                        <Label htmlFor="sports">Sports or activities you practice</Label>
                        <span className={`text-xs ${planData.sports.length >= 150 ? "text-red-500" : "text-slate-400"}`}>
                            {planData.sports.length}/150
                        </span>
                    </div>
                    <Input
                        id="sports"
                        placeholder="e.g., Football, cycling, tennis, swimming..."
                        value={planData.sports}
                        onChange={(e) => setPlanData({ ...planData, sports: e.target.value })}
                        maxLength={150}
                    />
                </div>
                <div className="space-y-2">
                    <div className="flex items-center justify-between">
                        <Label htmlFor="preferredExercises">Exercises you'd like to include or avoid</Label>
                        <span className={`text-xs ${planData.preferredExercises.length >= 300 ? "text-red-500" : "text-slate-400"}`}>
                            {planData.preferredExercises.length}/300
                        </span>
                    </div>
                    <Textarea
                        id="preferredExercises"
                        placeholder="e.g., Include: pull-ups, deadlifts. Avoid: running, burpees..."
                        value={planData.preferredExercises}
                        onChange={(e) => setPlanData({ ...planData, preferredExercises: e.target.value })}
                        className="resize-none"
                        rows={3}
                        maxLength={300}
                    />
                </div>
                <div className="space-y-2">
                    <div className="flex items-center justify-between">
                        <Label htmlFor="additionalNotes">Any other requests or notes</Label>
                        <span className={`text-xs ${planData.additionalNotes.length >= 400 ? "text-red-500" : "text-slate-400"}`}>
                            {planData.additionalNotes.length}/400
                        </span>
                    </div>
                    <Textarea
                        id="additionalNotes"
                        placeholder="e.g., I travel often and need hotel-friendly workouts, I prefer morning sessions..."
                        value={planData.additionalNotes}
                        onChange={(e) => setPlanData({ ...planData, additionalNotes: e.target.value })}
                        className="resize-none"
                        rows={3}
                        maxLength={400}
                    />
                </div>
                <Button onClick={handleDetails} className="w-full bg-gradient-to-r from-blue-600 to-indigo-600">
                    Generate My Plan <Sparkles className="w-4 h-4 ml-2" />
                </Button>
                <Button variant="outline" className="w-full" onClick={handleDetails}>
                    Skip <ArrowRight className="w-4 h-4 ml-2" />
                </Button>
            </div>
        );
    }

    if (step === 7) {
        return (
            <div className="space-y-3">
                <Button className="w-full bg-gradient-to-r from-blue-600 to-indigo-600">
                    View Complete Plan <ChevronRight className="w-4 h-4 ml-2" />
                </Button>
                <Button variant="outline" className="w-full" onClick={handleReset}>
                    Generate Another Plan
                </Button>
            </div>
        );
    }

    return null;
}
