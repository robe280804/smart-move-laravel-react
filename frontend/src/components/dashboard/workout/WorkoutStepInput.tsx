import { useNavigate } from "react-router-dom";
import {
    AlertTriangle,
    ArrowLeft,
    ArrowRight,
    Check,
    ChevronDown,
    ChevronRight,
    Info,
    Sparkles,
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Checkbox } from "@/components/ui/checkbox";
import { DAYS_OF_WEEK, EQUIPMENT_OPTIONS, FITNESS_GOALS, STRENGTH_FOCUSED_GOALS } from "@/constants/const";
import type { WorkoutPlanData } from "@/types/workout";
import { sanitizeTextInput, TEXT_MAX_LENGTHS } from "@/lib/sanitize";
import { WorkoutGeneratingStatus } from "@/components/dashboard/workout/WorkoutGeneratingStatus";

const BODYWEIGHT_ONLY = "Bodyweight Only" as const;

interface WorkoutStepInputProps {
    step: number;
    planData: WorkoutPlanData;
    setPlanData: (data: WorkoutPlanData) => void;
    handleGoalToggle: (goal: string) => void;
    handleBack: () => void;
    handleGoals: () => void;
    handleSchedule: () => void;
    handleEquipment: () => void;
    handleDetails: () => void;
    handleReset: () => void;
    setShowAllGoals: (show: boolean) => void;
    generatedPlanId: number | null;
    generationFailed: boolean;
}

function StepNav({
    onBack,
    onContinue,
    continueLabel = "Continue",
    continueIcon = <ArrowRight className="w-4 h-4 ml-2" />,
    disabled = false,
}: {
    onBack?: () => void;
    onContinue: () => void;
    continueLabel?: string;
    continueIcon?: React.ReactNode;
    disabled?: boolean;
}) {
    return (
        <div className="flex gap-3 pt-2">
            {onBack && (
                <Button variant="outline" onClick={onBack} className="flex-shrink-0">
                    <ArrowLeft className="w-4 h-4 mr-1" /> Back
                </Button>
            )}
            <Button
                onClick={onContinue}
                className="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600"
                disabled={disabled}
            >
                {continueLabel}
                {continueIcon}
            </Button>
        </div>
    );
}

// ─── Step 0: Goal ────────────────────────────────────────────────────────────

function GoalStep({
    planData,
    handleGoalToggle,
    handleGoals,
    setShowAllGoals,
}: Pick<WorkoutStepInputProps, "planData" | "handleGoalToggle" | "handleGoals" | "setShowAllGoals">) {
    return (
        <div className="space-y-4">
            <div className="grid sm:grid-cols-2 gap-3">
                {FITNESS_GOALS.slice(0, 4).map((goal) => {
                    const isSelected = planData.fitnessGoals === goal.value;
                    return (
                        <button
                            key={goal.value}
                            onClick={() => handleGoalToggle(goal.value)}
                            className={`p-4 border-2 rounded-xl transition-all text-left ${
                                isSelected
                                    ? "border-blue-600 bg-blue-50"
                                    : "border-slate-200 hover:border-blue-300 hover:bg-blue-50/50"
                            }`}
                        >
                            <div className="flex items-start gap-3">
                                <span className="text-2xl">{goal.icon}</span>
                                <div className="flex-1 min-w-0">
                                    <p className={`font-semibold text-sm ${isSelected ? "text-blue-600" : "text-slate-900"}`}>
                                        {goal.label}
                                    </p>
                                    <p className="text-xs text-slate-500 mt-0.5">{goal.description}</p>
                                </div>
                                {isSelected && <Check className="w-4 h-4 text-blue-600 flex-shrink-0 mt-0.5" />}
                            </div>
                        </button>
                    );
                })}
            </div>
            <Button variant="outline" className="w-full" onClick={() => setShowAllGoals(true)}>
                View all goals <ChevronDown className="w-4 h-4 ml-2" />
            </Button>
            <StepNav onContinue={handleGoals} disabled={!planData.fitnessGoals} />
        </div>
    );
}

// ─── Step 1: Schedule ────────────────────────────────────────────────────────

function ScheduleStep({
    planData,
    setPlanData,
    handleBack,
    handleSchedule,
}: Pick<WorkoutStepInputProps, "planData" | "setPlanData" | "handleBack" | "handleSchedule">) {
    return (
        <div className="space-y-5">
            <div className="grid sm:grid-cols-2 gap-4">
                <div className="space-y-2">
                    <Label htmlFor="trainingDays">Training days per week</Label>
                    <Input
                        id="trainingDays"
                        type="number"
                        min="1"
                        max="7"
                        value={planData.trainingDaysPerWeek}
                        onChange={(e) => {
                            const val = parseInt(e.target.value);
                            if (!isNaN(val)) setPlanData({ ...planData, trainingDaysPerWeek: val });
                        }}
                        onBlur={(e) => {
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
                        onChange={(e) => setPlanData({ ...planData, sessionDuration: e.target.value })}
                        onBlur={(e) => {
                            const val = Math.max(15, Math.min(180, parseInt(e.target.value) || 60));
                            setPlanData({ ...planData, sessionDuration: String(val) });
                        }}
                    />
                </div>
            </div>
            <div className="space-y-2">
                <Label>Available days of the week</Label>
                <div className="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    {DAYS_OF_WEEK.map((day) => (
                        <label
                            key={day}
                            className={`flex items-center gap-2 p-3 border-2 rounded-lg cursor-pointer transition-all ${
                                planData.availableDays.includes(day)
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
            <StepNav onBack={handleBack} onContinue={handleSchedule} />
        </div>
    );
}

// ─── Step 2: Equipment ───────────────────────────────────────────────────────

function EquipmentStep({
    planData,
    setPlanData,
    handleBack,
    handleEquipment,
}: Pick<WorkoutStepInputProps, "planData" | "setPlanData" | "handleBack" | "handleEquipment">) {
    const isBodyweightOnly =
        planData.equipment.length > 0 &&
        planData.equipment.every(e => e === BODYWEIGHT_ONLY);

    const isStrengthGoal = (STRENGTH_FOCUSED_GOALS as readonly string[]).includes(planData.fitnessGoals);
    const showConflictWarning = isStrengthGoal && isBodyweightOnly;

    const hasEverything = planData.equipment.includes("everything");
    const equipmentLocked = planData.gymAccess || hasEverything;

    const nonBodyweightOptions = EQUIPMENT_OPTIONS.filter(e => e !== BODYWEIGHT_ONLY);

    const handleGymAccessChange = (checked: boolean) => {
        if (checked) {
            setPlanData({
                ...planData,
                gymAccess: true,
                equipment: [...nonBodyweightOptions, "everything"],
            });
        } else {
            setPlanData({ ...planData, gymAccess: false, equipment: [] });
        }
    };

    const handleEverythingToggle = (checked: boolean) => {
        if (checked) {
            setPlanData({ ...planData, equipment: [...nonBodyweightOptions, "everything"] });
        } else {
            setPlanData({ ...planData, equipment: [] });
        }
    };

    const handleBodyweightToggle = (checked: boolean) => {
        if (checked) {
            // Bodyweight only: clear all other equipment and gym access
            setPlanData({ ...planData, gymAccess: false, equipment: [BODYWEIGHT_ONLY] });
        } else {
            setPlanData({ ...planData, equipment: [] });
        }
    };

    const handleItemToggle = (item: string, checked: boolean) => {
        if (checked) {
            // Adding any real equipment removes "Bodyweight Only"
            const updated = planData.equipment
                .filter(e => e !== BODYWEIGHT_ONLY)
                .concat(item);
            setPlanData({ ...planData, equipment: updated });
        } else {
            setPlanData({ ...planData, equipment: planData.equipment.filter(e => e !== item) });
        }
    };

    return (
        <div className="space-y-4">
            {/* Gym access shortcut */}
            <label className="flex items-center gap-3 p-4 bg-blue-50 border border-blue-200 rounded-lg cursor-pointer">
                <Checkbox
                    id="gymAccess"
                    checked={planData.gymAccess}
                    onCheckedChange={(checked) => handleGymAccessChange(checked as boolean)}
                />
                <div>
                    <p className="font-medium text-slate-900 text-sm">I have gym access</p>
                    <p className="text-xs text-slate-500 mt-0.5">Selects all available equipment automatically</p>
                </div>
            </label>

            <div className="space-y-2">
                <Label>Available equipment</Label>
                <div className="grid sm:grid-cols-2 gap-2">
                    {/* "Everything" shortcut */}
                    <label
                        className={`flex items-center gap-2 p-3 border-2 rounded-lg sm:col-span-2 transition-all ${
                            hasEverything || planData.gymAccess
                                ? "border-blue-600 bg-blue-50 cursor-not-allowed"
                                : "border-blue-300 bg-blue-50/50 hover:border-blue-500 cursor-pointer"
                        }`}
                    >
                        <Checkbox
                            checked={hasEverything || planData.gymAccess}
                            disabled={planData.gymAccess}
                            onCheckedChange={(checked) => handleEverythingToggle(checked as boolean)}
                        />
                        <span className="text-sm font-semibold text-blue-700">Everything (all equipment)</span>
                    </label>

                    {/* Regular equipment items (excluding Bodyweight Only) */}
                    {nonBodyweightOptions.map((equipment) => (
                        <label
                            key={equipment}
                            className={`flex items-center gap-2 p-3 border-2 rounded-lg transition-all ${
                                equipmentLocked
                                    ? "border-blue-600 bg-blue-50 cursor-not-allowed opacity-70"
                                    : planData.equipment.includes(equipment)
                                        ? "border-blue-600 bg-blue-50 cursor-pointer"
                                        : "border-slate-200 hover:border-slate-300 cursor-pointer"
                            }`}
                        >
                            <Checkbox
                                checked={equipmentLocked || planData.equipment.includes(equipment)}
                                disabled={equipmentLocked}
                                onCheckedChange={(checked) => handleItemToggle(equipment, checked as boolean)}
                            />
                            <span className="text-sm font-medium">{equipment}</span>
                        </label>
                    ))}

                    {/* Bodyweight Only — mutually exclusive with all others */}
                    <label
                        className={`flex items-center gap-2 p-3 border-2 rounded-lg sm:col-span-2 transition-all ${
                            equipmentLocked
                                ? "border-slate-200 bg-slate-50 cursor-not-allowed opacity-50"
                                : isBodyweightOnly
                                    ? "border-slate-600 bg-slate-50 cursor-pointer"
                                    : "border-slate-200 hover:border-slate-400 cursor-pointer"
                        }`}
                    >
                        <Checkbox
                            checked={isBodyweightOnly}
                            disabled={equipmentLocked}
                            onCheckedChange={(checked) => handleBodyweightToggle(checked as boolean)}
                        />
                        <div className="flex-1 min-w-0">
                            <span className="text-sm font-medium">{BODYWEIGHT_ONLY}</span>
                            <span className="text-xs text-slate-500 ml-2">— no equipment available</span>
                        </div>
                    </label>
                </div>
            </div>

            {/* Conflict warning: strength goal + bodyweight only */}
            {showConflictWarning && (
                <div className="flex gap-3 p-4 bg-amber-50 border border-amber-200 rounded-xl">
                    <AlertTriangle className="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" />
                    <div className="space-y-1">
                        <p className="text-sm font-semibold text-amber-900">Heads up</p>
                        <p className="text-sm text-amber-800">
                            Your goal (<span className="font-medium">{FITNESS_GOALS.find(g => g.value === planData.fitnessGoals)?.label}</span>) typically
                            benefits from resistance equipment. With bodyweight only, your plan will focus on
                            calisthenics — still effective, but different from traditional strength training.
                            You can continue or add some equipment above.
                        </p>
                    </div>
                </div>
            )}

            <StepNav onBack={handleBack} onContinue={handleEquipment} />
        </div>
    );
}

// ─── Step 3: Preferences (injuries + optional details) ───────────────────────

function PreferencesStep({
    planData,
    setPlanData,
    handleBack,
    handleDetails,
}: Pick<WorkoutStepInputProps, "planData" | "setPlanData" | "handleBack" | "handleDetails">) {
    return (
        <div className="space-y-4">
            <div className="flex gap-3 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                <Info className="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />
                <p className="text-sm text-blue-800">
                    All fields are optional — the more context you provide, the more tailored your plan will be.
                </p>
            </div>

            {/* Injuries */}
            <div className="space-y-2">
                <div className="flex items-center justify-between">
                    <Label htmlFor="injuries">Injuries or physical limitations</Label>
                    <span className={`text-xs ${planData.injuries.length >= TEXT_MAX_LENGTHS.injuries ? "text-red-500" : "text-slate-400"}`}>
                        {planData.injuries.length}/{TEXT_MAX_LENGTHS.injuries}
                    </span>
                </div>
                <Input
                    id="injuries"
                    placeholder="e.g., Lower back pain, knee injury... (leave blank if none)"
                    value={planData.injuries}
                    onChange={(e) =>
                        setPlanData({
                            ...planData,
                            injuries: sanitizeTextInput(e.target.value, TEXT_MAX_LENGTHS.injuries),
                        })
                    }
                    maxLength={TEXT_MAX_LENGTHS.injuries}
                />
            </div>

            {/* Sports */}
            <div className="space-y-2">
                <div className="flex items-center justify-between">
                    <Label htmlFor="sports">Sports or activities you practice</Label>
                    <span className={`text-xs ${planData.sports.length >= TEXT_MAX_LENGTHS.sports ? "text-red-500" : "text-slate-400"}`}>
                        {planData.sports.length}/{TEXT_MAX_LENGTHS.sports}
                    </span>
                </div>
                <Input
                    id="sports"
                    placeholder="e.g., Football, cycling, tennis..."
                    value={planData.sports}
                    onChange={(e) =>
                        setPlanData({ ...planData, sports: sanitizeTextInput(e.target.value, TEXT_MAX_LENGTHS.sports) })
                    }
                    maxLength={TEXT_MAX_LENGTHS.sports}
                />
            </div>

            {/* Preferred exercises */}
            <div className="space-y-2">
                <div className="flex items-center justify-between">
                    <Label htmlFor="preferredExercises">Exercises to include or avoid</Label>
                    <span className={`text-xs ${planData.preferredExercises.length >= TEXT_MAX_LENGTHS.preferredExercises ? "text-red-500" : "text-slate-400"}`}>
                        {planData.preferredExercises.length}/{TEXT_MAX_LENGTHS.preferredExercises}
                    </span>
                </div>
                <Textarea
                    id="preferredExercises"
                    placeholder="e.g., Include: pull-ups, deadlifts. Avoid: running..."
                    value={planData.preferredExercises}
                    onChange={(e) =>
                        setPlanData({
                            ...planData,
                            preferredExercises: sanitizeTextInput(e.target.value, TEXT_MAX_LENGTHS.preferredExercises),
                        })
                    }
                    className="resize-none"
                    rows={3}
                    maxLength={TEXT_MAX_LENGTHS.preferredExercises}
                />
            </div>

            {/* Additional notes */}
            <div className="space-y-2">
                <div className="flex items-center justify-between">
                    <Label htmlFor="additionalNotes">Any other requests or notes</Label>
                    <span className={`text-xs ${planData.additionalNotes.length >= TEXT_MAX_LENGTHS.additionalNotes ? "text-red-500" : "text-slate-400"}`}>
                        {planData.additionalNotes.length}/{TEXT_MAX_LENGTHS.additionalNotes}
                    </span>
                </div>
                <Textarea
                    id="additionalNotes"
                    placeholder="e.g., I travel often and need hotel-friendly workouts..."
                    value={planData.additionalNotes}
                    onChange={(e) =>
                        setPlanData({
                            ...planData,
                            additionalNotes: sanitizeTextInput(e.target.value, TEXT_MAX_LENGTHS.additionalNotes),
                        })
                    }
                    className="resize-none"
                    rows={3}
                    maxLength={TEXT_MAX_LENGTHS.additionalNotes}
                />
            </div>

            <StepNav
                onBack={handleBack}
                onContinue={handleDetails}
                continueLabel="Generate My Plan"
                continueIcon={<Sparkles className="w-4 h-4 ml-2" />}
            />
        </div>
    );
}

// ─── Step 5: Completion ───────────────────────────────────────────────────────

function CompletionStep({
    generatedPlanId,
    generationFailed,
    handleReset,
}: Pick<WorkoutStepInputProps, "generatedPlanId" | "generationFailed" | "handleReset">) {
    const navigate = useNavigate();

    if (generationFailed) {
        return (
            <div className="flex flex-col items-center justify-center py-12 gap-5 text-center">
                <div className="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center">
                    <AlertTriangle className="w-8 h-8 text-red-500" />
                </div>
                <div>
                    <h3 className="text-lg font-semibold text-slate-900">Generation Failed</h3>
                    <p className="text-slate-500 text-sm mt-1 max-w-xs">
                        Something went wrong while generating your plan. Please try again.
                    </p>
                </div>
                <Button variant="outline" className="w-full max-w-xs" onClick={handleReset}>
                    Try Again
                </Button>
            </div>
        );
    }

    return (
        <div className="flex flex-col items-center justify-center py-12 gap-5 text-center">
            <div className="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center">
                <Check className="w-8 h-8 text-green-600" />
            </div>
            <div>
                <h3 className="text-lg font-semibold text-slate-900">Your Plan is Ready!</h3>
                <p className="text-slate-500 text-sm mt-1 max-w-xs">
                    Your personalized workout plan has been generated successfully.
                </p>
            </div>
            <div className="flex flex-col gap-3 w-full max-w-xs">
                {generatedPlanId && (
                    <Button
                        className="w-full bg-gradient-to-r from-blue-600 to-indigo-600"
                        onClick={() => navigate(`/dashboard/workouts/${generatedPlanId}`)}
                    >
                        View Complete Plan <ChevronRight className="w-4 h-4 ml-2" />
                    </Button>
                )}
                <Button variant="outline" className="w-full" onClick={handleReset}>
                    Generate Another Plan
                </Button>
            </div>
        </div>
    );
}

// ─── Root dispatcher ─────────────────────────────────────────────────────────

export function WorkoutStepInput(props: WorkoutStepInputProps) {
    const { step } = props;

    if (step === 0) return <GoalStep {...props} />;
    if (step === 1) return <ScheduleStep {...props} />;
    if (step === 2) return <EquipmentStep {...props} />;
    if (step === 3) return <PreferencesStep {...props} />;
    if (step === 4) return <WorkoutGeneratingStatus />;
    if (step === 5) return <CompletionStep {...props} />;

    return null;
}
