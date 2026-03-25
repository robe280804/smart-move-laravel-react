import { useState } from "react";
import { useNavigate, Link } from "react-router-dom";
import {
    AlertTriangle,
    ArrowLeft,
    ArrowRight,
    ArrowUpFromLine,
    Armchair,
    Bike,
    Cable,
    Check,
    ChevronDown,
    ChevronRight,
    CircleDot,
    CreditCard,
    Dumbbell,
    Info,
    LucideIcon,
    MailCheck,
    PersonStanding,
    Sparkles,
    Waves,
    Zap,
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
import type { GenerationFailureReason } from "@/hooks/useWorkoutPlanGenerator";

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
    failureReason: GenerationFailureReason | null;
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
    const [daysInputValue, setDaysInputValue] = useState(String(planData.trainingDaysPerWeek));

    return (
        <div className="space-y-5">
            <div className="grid sm:grid-cols-2 gap-4">
                <div className="space-y-2">
                    <Label htmlFor="trainingDays">Training days per week</Label>
                    <Input
                        id="trainingDays"
                        type="number"
                        inputMode="numeric"
                        min="1"
                        max="7"
                        value={daysInputValue}
                        onChange={(e) => {
                            setDaysInputValue(e.target.value);
                            const val = parseInt(e.target.value);
                            if (!isNaN(val) && val >= 1 && val <= 7) {
                                setPlanData({ ...planData, trainingDaysPerWeek: val });
                            }
                        }}
                        onBlur={() => {
                            const val = Math.max(1, Math.min(7, parseInt(daysInputValue) || 1));
                            setDaysInputValue(String(val));
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

type EquipmentMode = "gym" | "home" | "bodyweight" | null;

const EQUIPMENT_ICONS: Record<string, LucideIcon> = {
    "Dumbbells": Dumbbell,
    "Barbells": Dumbbell,
    "Resistance Bands": Waves,
    "Pull-up Bar": ArrowUpFromLine,
    "Bench": Armchair,
    "Kettlebells": CircleDot,
    "Cable Machine": Cable,
    "Cardio Equipment": Bike,
    "Bodyweight Only": PersonStanding,
};

const EQUIPMENT_MODES = [
    {
        key: "gym" as const,
        icon: "🏢",
        title: "Full Gym",
        shortTitle: "Gym",
        description: "I train at a fully equipped gym",
    },
    {
        key: "home" as const,
        icon: "🏠",
        title: "Home / Limited",
        shortTitle: "Home",
        description: "I have some equipment at home",
    },
    {
        key: "bodyweight" as const,
        icon: "🏃",
        title: "Bodyweight Only",
        shortTitle: "No Equip.",
        description: "No equipment needed",
    },
];

function initialMode(planData: WorkoutPlanData): EquipmentMode {
    if (planData.gymAccess) return "gym";
    if (planData.equipment.length === 1 && planData.equipment[0] === BODYWEIGHT_ONLY) return "bodyweight";
    if (planData.equipment.length > 0) return "home";
    return null;
}

function EquipmentStep({
    planData,
    setPlanData,
    handleBack,
    handleEquipment,
}: Pick<WorkoutStepInputProps, "planData" | "setPlanData" | "handleBack" | "handleEquipment">) {
    const nonBodyweightOptions = EQUIPMENT_OPTIONS.filter(e => e !== BODYWEIGHT_ONLY);
    const [mode, setMode] = useState<EquipmentMode>(() => initialMode(planData));

    const isStrengthGoal = (STRENGTH_FOCUSED_GOALS as readonly string[]).includes(planData.fitnessGoals);
    const showConflictWarning = isStrengthGoal && mode === "bodyweight";

    const selectMode = (newMode: EquipmentMode) => {
        setMode(newMode);
        if (newMode === "gym") {
            setPlanData({
                ...planData,
                gymAccess: true,
                equipment: [...nonBodyweightOptions, "everything"],
            });
        } else if (newMode === "bodyweight") {
            setPlanData({ ...planData, gymAccess: false, equipment: [BODYWEIGHT_ONLY] });
        } else if (newMode === "home") {
            setPlanData({ ...planData, gymAccess: false, equipment: [] });
        }
    };

    const handleItemToggle = (item: string) => {
        const has = planData.equipment.includes(item);
        if (has) {
            setPlanData({ ...planData, equipment: planData.equipment.filter(e => e !== item) });
        } else {
            setPlanData({ ...planData, equipment: [...planData.equipment, item] });
        }
    };

    return (
        <div className="space-y-4">
            {/* Mode selector */}
            <div className="grid grid-cols-3 gap-2 sm:gap-3">
                {EQUIPMENT_MODES.map((m) => {
                    const isSelected = mode === m.key;
                    return (
                        <button
                            key={m.key}
                            onClick={() => selectMode(m.key)}
                            className={`p-2 sm:p-4 border-2 rounded-xl transition-all text-center ${
                                isSelected
                                    ? "border-blue-600 bg-blue-50"
                                    : "border-slate-200 hover:border-blue-300 hover:bg-blue-50/50"
                            }`}
                        >
                            <span className="text-xl sm:text-3xl block">{m.icon}</span>
                            <p className={`font-semibold text-[11px] sm:text-sm mt-1 sm:mt-2 leading-tight ${isSelected ? "text-blue-600" : "text-slate-900"}`}>
                                <span className="sm:hidden">{m.shortTitle}</span>
                                <span className="hidden sm:inline">{m.title}</span>
                            </p>
                            <p className="text-[10px] sm:text-xs text-slate-500 mt-0.5 hidden sm:block">{m.description}</p>
                        </button>
                    );
                })}
            </div>

            {/* Equipment picker — only shown for "home" mode */}
            {mode === "home" && (
                <div className="space-y-3">
                    <Label>Select your equipment</Label>
                    <div className="grid grid-cols-2 gap-3">
                        {nonBodyweightOptions.map((equipment) => {
                            const isSelected = planData.equipment.includes(equipment);
                            return (
                                <button
                                    key={equipment}
                                    onClick={() => handleItemToggle(equipment)}
                                    className={`relative p-3 sm:p-4 border-2 rounded-xl transition-all text-center ${
                                        isSelected
                                            ? "border-blue-600 bg-blue-50"
                                            : "border-slate-200 hover:border-blue-300 hover:bg-blue-50/50"
                                    }`}
                                >
                                    {isSelected && (
                                        <Check className="w-4 h-4 text-blue-600 absolute top-2 right-2" />
                                    )}
                                    {(() => { const Icon = EQUIPMENT_ICONS[equipment] ?? Dumbbell; return <Icon className="w-7 h-7 sm:w-9 sm:h-9 mx-auto" />; })()}
                                    <p className={`font-semibold text-xs sm:text-sm mt-1.5 leading-tight ${isSelected ? "text-blue-600" : "text-slate-900"}`}>
                                        {equipment}
                                    </p>
                                </button>
                            );
                        })}
                    </div>
                </div>
            )}

            {/* Gym mode confirmation */}
            {mode === "gym" && (
                <div className="flex gap-3 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                    <Check className="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />
                    <p className="text-sm text-blue-800">
                        All equipment will be available for your plan — the AI will pick the best exercises from the full range of gym machines and free weights.
                    </p>
                </div>
            )}

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
            <div className="space-y-1.5">
                <Label htmlFor="injuries">Injuries or physical limitations</Label>
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
                <p className={`text-xs text-right ${planData.injuries.length >= TEXT_MAX_LENGTHS.injuries ? "text-red-500" : "text-slate-400"}`}>
                    {planData.injuries.length}/{TEXT_MAX_LENGTHS.injuries}
                </p>
            </div>

            {/* Sports */}
            <div className="space-y-1.5">
                <Label htmlFor="sports">Sports or activities you practice</Label>
                <Input
                    id="sports"
                    placeholder="e.g., Football, cycling, tennis..."
                    value={planData.sports}
                    onChange={(e) =>
                        setPlanData({ ...planData, sports: sanitizeTextInput(e.target.value, TEXT_MAX_LENGTHS.sports) })
                    }
                    maxLength={TEXT_MAX_LENGTHS.sports}
                />
                <p className={`text-xs text-right ${planData.sports.length >= TEXT_MAX_LENGTHS.sports ? "text-red-500" : "text-slate-400"}`}>
                    {planData.sports.length}/{TEXT_MAX_LENGTHS.sports}
                </p>
            </div>

            {/* Preferred exercises */}
            <div className="space-y-1.5">
                <Label htmlFor="preferredExercises">Exercises to include or avoid</Label>
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
                <p className={`text-xs text-right ${planData.preferredExercises.length >= TEXT_MAX_LENGTHS.preferredExercises ? "text-red-500" : "text-slate-400"}`}>
                    {planData.preferredExercises.length}/{TEXT_MAX_LENGTHS.preferredExercises}
                </p>
            </div>

            {/* Additional notes */}
            <div className="space-y-1.5">
                <Label htmlFor="additionalNotes">Any other requests or notes</Label>
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
                <p className={`text-xs text-right ${planData.additionalNotes.length >= TEXT_MAX_LENGTHS.additionalNotes ? "text-red-500" : "text-slate-400"}`}>
                    {planData.additionalNotes.length}/{TEXT_MAX_LENGTHS.additionalNotes}
                </p>
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
    failureReason,
    handleReset,
}: Pick<WorkoutStepInputProps, "generatedPlanId" | "generationFailed" | "failureReason" | "handleReset">) {
    const navigate = useNavigate();

    if (generationFailed) {
        if (failureReason === "email_not_verified") {
            return (
                <div className="flex flex-col items-center justify-center py-12 gap-5 text-center">
                    <div className="w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center">
                        <MailCheck className="w-8 h-8 text-amber-500" />
                    </div>
                    <div>
                        <h3 className="text-lg font-semibold text-slate-900">Email Not Verified</h3>
                        <p className="text-slate-500 text-sm mt-1 max-w-xs">
                            You need to verify your email address before generating a workout plan.
                        </p>
                    </div>
                    <div className="flex flex-col gap-3 w-full max-w-xs">
                        <Link to="/dashboard/profile?tab=security">
                            <Button className="w-full bg-gradient-to-r from-amber-500 to-orange-500">
                                <MailCheck className="w-4 h-4 mr-2" />
                                Verify Your Email
                            </Button>
                        </Link>
                        <Button variant="outline" className="w-full" onClick={handleReset}>
                            Go Back
                        </Button>
                    </div>
                </div>
            );
        }

        if (failureReason === "plan_limit") {
            return (
                <div className="flex flex-col items-center justify-center py-12 gap-5 text-center">
                    <div className="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center">
                        <Zap className="w-8 h-8 text-indigo-500" />
                    </div>
                    <div>
                        <h3 className="text-lg font-semibold text-slate-900">Generation Limit Reached</h3>
                        <p className="text-slate-500 text-sm mt-1 max-w-xs">
                            You've used all your available plan generations. Upgrade your plan to generate more.
                        </p>
                    </div>
                    <div className="flex flex-col gap-3 w-full max-w-xs">
                        <Link to="/dashboard/profile?tab=subscription">
                            <Button className="w-full bg-gradient-to-r from-blue-600 to-indigo-600">
                                <Zap className="w-4 h-4 mr-2" />
                                Upgrade Your Plan
                            </Button>
                        </Link>
                        <Button variant="outline" className="w-full" onClick={handleReset}>
                            Go Back
                        </Button>
                    </div>
                </div>
            );
        }

        if (failureReason === "credits_exhausted") {
            return (
                <div className="flex flex-col items-center justify-center py-12 gap-5 text-center">
                    <div className="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center">
                        <CreditCard className="w-8 h-8 text-red-500" />
                    </div>
                    <div>
                        <h3 className="text-lg font-semibold text-slate-900">Service Temporarily Unavailable</h3>
                        <p className="text-slate-500 text-sm mt-1 max-w-xs">
                            The AI service is temporarily unavailable. Our team has been notified and is working on a fix. Please try again later.
                        </p>
                    </div>
                    <Button variant="outline" className="w-full max-w-xs" onClick={handleReset}>
                        Try Again Later
                    </Button>
                </div>
            );
        }

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
