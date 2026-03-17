import { useState } from "react";
import { useParams, Link } from "react-router";
import { ArrowLeft, Save, Dumbbell, Calendar, Activity, Trophy, Download } from "lucide-react";
import { Button } from "@/components/ui/button";
import { FITNESS_GOALS, WORKOUT_TYPES, EXPERIENCE_LEVELS, DAYS_OF_WEEK } from "@/constants/const";
import { useWorkoutPlan } from "@/hooks/useWorkoutPlan";
import { PlanOverviewCards } from "@/components/dashboard/workout/detail/PlanOverviewCards";
import { WorkoutDayAccordion } from "@/components/dashboard/workout/detail/WorkoutDayAccordion";
import { PDFDownloadLink } from "@react-pdf/renderer";
import { WorkoutPlanPdf } from "@/components/dashboard/workout/detail/WorkoutPlanPdf";

const GOAL_LABEL = Object.fromEntries(FITNESS_GOALS.map((g) => [g.value, g.label]));
const GOAL_ICON = Object.fromEntries(FITNESS_GOALS.map((g) => [g.value, g.icon]));
const WORKOUT_TYPE_LABEL = Object.fromEntries(WORKOUT_TYPES.map((w) => [w.value, w.label]));
const EXPERIENCE_LABEL = Object.fromEntries(
    EXPERIENCE_LEVELS.map((l) => [l, l.charAt(0).toUpperCase() + l.slice(1)]),
);

const EXPERIENCE_BADGE: Record<string, string> = {
    beginner: "bg-green-500/20 text-green-300",
    intermediate: "bg-blue-500/20 text-blue-300",
    advanced: "bg-purple-500/20 text-purple-300",
};

const getDayName = (day: number): string => DAYS_OF_WEEK[day - 1] ?? "Unknown";

const DetailSkeleton = () => (
    <div className="space-y-4 animate-pulse">
        <div className="rounded-2xl bg-slate-200 h-40" />
        <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
            {Array.from({ length: 3 }).map((_, i) => (
                <div key={i} className="h-20 bg-slate-200 rounded-lg" />
            ))}
        </div>
        {Array.from({ length: 3 }).map((_, i) => (
            <div key={i} className="h-24 bg-slate-200 rounded-xl" />
        ))}
    </div>
);

export const WorkoutPlanDetail = () => {
    const { id } = useParams<{ id: string }>();
    const planId = Number(id);
    const { plan, isLoading, error, hasChanges, updateExercise, refetch } = useWorkoutPlan(planId);
    const [expandedDays, setExpandedDays] = useState<number[]>([]);

    const toggleDay = (dayId: number) =>
        setExpandedDays((prev) =>
            prev.includes(dayId) ? prev.filter((d) => d !== dayId) : [...prev, dayId],
        );

    if (isLoading) {
        return <DetailSkeleton />;
    }

    if (error || !plan) {
        return (
            <div className="flex items-center justify-center py-24">
                <div className="text-center">
                    <h2 className="text-2xl font-semibold text-slate-900 mb-2">
                        {!plan && !error ? "Workout Plan Not Found" : "Something went wrong"}
                    </h2>
                    <p className="text-slate-600 mb-4">
                        {error ?? "The workout plan you're looking for doesn't exist."}
                    </p>
                    <div className="flex items-center justify-center gap-3">
                        <Link to="/dashboard/workouts">
                            <Button variant="outline">Back to Workout Plans</Button>
                        </Link>
                        {error && <Button onClick={refetch}>Retry</Button>}
                    </div>
                </div>
            </div>
        );
    }

    const totalSessions = plan.plan_days.length;
    const totalExercises = plan.plan_days.reduce(
        (acc, day) => acc + day.workout_blocks.reduce((a, b) => a + b.block_exercises.length, 0),
        0,
    );

    return (
        <div>
            {/* Header banner */}
            <div className="relative rounded-2xl overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-900 p-6 mb-6">
                {/* Decorative blobs */}
                <div className="absolute top-0 right-0 w-72 h-72 bg-indigo-500/10 rounded-full -translate-y-1/2 translate-x-1/4 pointer-events-none" />
                <div className="absolute bottom-0 left-1/4 w-48 h-48 bg-blue-500/10 rounded-full translate-y-1/2 pointer-events-none" />

                <div className="relative">
                    {/* Back link */}
                    <Link
                        to="/dashboard/workouts"
                        className="inline-flex items-center gap-1.5 text-slate-400 hover:text-white text-sm mb-4 transition-colors"
                    >
                        <ArrowLeft className="w-4 h-4" />
                        Back to Workout Plans
                    </Link>

                    <div className="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div>
                            <div className="flex items-center gap-2 mb-2">
                                <div className="w-8 h-8 rounded-lg bg-indigo-500/20 flex items-center justify-center text-base">
                                    {GOAL_ICON[plan.goal] ?? "🏋️"}
                                </div>
                                <span className="text-indigo-300 text-xs font-semibold uppercase tracking-widest">
                                    Workout Plan
                                </span>
                            </div>
                            <h1 className="text-2xl font-bold text-white mb-1">
                                {GOAL_LABEL[plan.goal] ?? plan.goal}
                            </h1>
                            <div className="flex items-center gap-2">
                                <span className="text-slate-400 text-sm">
                                    {WORKOUT_TYPE_LABEL[plan.workout_type] ?? plan.workout_type}
                                </span>
                                <span className="text-slate-600">·</span>
                                <span
                                    className={`text-xs px-2 py-0.5 rounded-full font-medium ${EXPERIENCE_BADGE[plan.experience_level] ?? "bg-slate-500/20 text-slate-300"}`}
                                >
                                    {EXPERIENCE_LABEL[plan.experience_level] ??
                                        plan.experience_level}
                                </span>
                            </div>
                        </div>

                        <div className="flex items-center gap-2 flex-shrink-0">
                            <PDFDownloadLink
                                document={<WorkoutPlanPdf plan={plan} />}
                                fileName={`workout-plan-${plan.id}.pdf`}
                            >
                                {({ loading }) => (
                                    <Button
                                        variant="outline"
                                        className="border-white/20 text-white bg-white/10 hover:bg-white/20 transition-colors"
                                        disabled={loading}
                                    >
                                        <Download className="w-4 h-4 mr-2" />
                                        {loading ? "Generating…" : "Export PDF"}
                                    </Button>
                                )}
                            </PDFDownloadLink>

                            <Button
                                disabled={!hasChanges}
                                className="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 disabled:bg-slate-700 disabled:text-slate-400 shadow-lg shadow-indigo-900/50"
                            >
                                <Save className="w-4 h-4" />
                                {hasChanges ? "Save Changes" : "No Changes"}
                            </Button>
                        </div>
                    </div>

                    {/* Stats row */}
                    <div className="flex flex-wrap items-center gap-5 mt-5 pt-5 border-t border-white/10">
                        <div className="flex items-center gap-2 text-slate-300">
                            <Calendar className="w-4 h-4 text-indigo-400" />
                            <span className="text-sm">
                                <span className="font-semibold text-white">
                                    {plan.training_days_per_week}
                                </span>{" "}
                                days/week
                            </span>
                        </div>
                        <div className="flex items-center gap-2 text-slate-300">
                            <Activity className="w-4 h-4 text-indigo-400" />
                            <span className="text-sm">
                                <span className="font-semibold text-white">{totalSessions}</span>{" "}
                                sessions
                            </span>
                        </div>
                        <div className="flex items-center gap-2 text-slate-300">
                            <Dumbbell className="w-4 h-4 text-indigo-400" />
                            <span className="text-sm">
                                <span className="font-semibold text-white">{totalExercises}</span>{" "}
                                exercises
                            </span>
                        </div>
                        <div className="flex items-center gap-2 text-slate-300">
                            <Trophy className="w-4 h-4 text-indigo-400" />
                            <span className="text-sm">
                                {plan.plan_days.map((d) => getDayName(d.day_of_week).slice(0, 3)).join(" · ")}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {/* Content 
            <PlanOverviewCards plan={plan} />
*/}

            <div className="space-y-4 mt-6">
                {plan.plan_days.map((day) => (
                    <WorkoutDayAccordion
                        key={day.id}
                        day={day}
                        isExpanded={expandedDays.includes(day.id)}
                        onToggle={() => toggleDay(day.id)}
                        onUpdate={updateExercise}
                    />
                ))}
            </div>
        </div>
    );
};
