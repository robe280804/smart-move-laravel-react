import { useState, useEffect, useRef } from "react";
import { Link } from "react-router";
import { notify } from "@/lib/toast";
import {
    Calendar,
    Dumbbell,
    Clock,
    TrendingUp,
    Plus,
    Trash2,
    Activity,
    ChevronRight,
    Zap,
    BarChart3,
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { FITNESS_GOALS, WORKOUT_TYPES, EXPERIENCE_LEVELS, DAYS_OF_WEEK } from "@/constants/const";
import { useWorkoutPlans } from "@/hooks/useWorkoutPlans";
import { useGeneratingPlans } from "@/hooks/useGeneratingPlans";
import { GeneratingWorkoutBanner } from "@/components/dashboard/GeneratingWorkoutBanner";

const GOAL_LABEL = Object.fromEntries(FITNESS_GOALS.map((g) => [g.value, g.label]));
const GOAL_ICON = Object.fromEntries(FITNESS_GOALS.map((g) => [g.value, g.icon]));
const WORKOUT_TYPE_LABEL = Object.fromEntries(WORKOUT_TYPES.map((w) => [w.value, w.label]));
const EXPERIENCE_LABEL = Object.fromEntries(
    EXPERIENCE_LEVELS.map((l) => [l, l.charAt(0).toUpperCase() + l.slice(1)]),
);

const GOAL_GRADIENT: Record<string, string> = {
    weight_loss: "from-orange-500 to-red-500",
    muscle_gain: "from-yellow-500 to-orange-500",
    strength_building: "from-purple-500 to-indigo-600",
    endurance: "from-blue-500 to-cyan-500",
    flexibility: "from-green-500 to-teal-500",
    general_fitness: "from-indigo-500 to-purple-500",
    body_recomposition: "from-pink-500 to-rose-500",
    athletic_performance: "from-cyan-500 to-blue-500",
    rehabilitation: "from-green-400 to-emerald-500",
    posture_correction: "from-teal-500 to-cyan-600",
    functional_fitness: "from-amber-500 to-orange-500",
};

const EXPERIENCE_BADGE: Record<string, string> = {
    beginner: "bg-green-100 text-green-700",
    intermediate: "bg-blue-100 text-blue-700",
    advanced: "bg-purple-100 text-purple-700",
};

// day_of_week: 1 = Monday … 7 = Sunday  →  DAYS_OF_WEEK is 0-indexed from Monday
const getDayName = (day: number): string => DAYS_OF_WEEK[day - 1] ?? "Unknown";
const getDayAbbr = (day: number): string => getDayName(day).slice(0, 3);

const PlanCardSkeleton = () => (
    <div className="bg-white rounded-xl border border-slate-200 overflow-hidden animate-pulse">
        <div className="h-2 bg-slate-200" />
        <div className="p-5 space-y-4">
            <div className="flex items-center gap-3">
                <div className="w-10 h-10 bg-slate-200 rounded-xl" />
                <div className="space-y-1.5 flex-1">
                    <div className="h-4 bg-slate-200 rounded w-1/2" />
                    <div className="h-3 bg-slate-200 rounded w-1/3" />
                </div>
            </div>
            <div className="flex gap-2">
                {Array.from({ length: 4 }).map((_, i) => (
                    <div key={i} className="h-8 bg-slate-200 rounded-lg flex-1" />
                ))}
            </div>
            <div className="h-9 bg-slate-200 rounded-lg" />
        </div>
    </div>
);

export const Workouts = () => {
    const { plans, isLoading, error, deletePlan, refetch } = useWorkoutPlans();
    const { completedPlans, failedPlans, dismissPlan } = useGeneratingPlans();
    const prevCompletedCount = useRef(completedPlans.length);
    const [confirmDeleteId, setConfirmDeleteId] = useState<number | null>(null);
    const [isDeleting, setIsDeleting] = useState(false);

    // On mount: dismiss any stale completed/failed banners from previous sessions
    useEffect(() => {
        completedPlans.forEach((p) => dismissPlan(p.id));
        failedPlans.forEach((p) => dismissPlan(p.id));
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    // When a plan finishes generating, refresh the list and auto-dismiss the banner
    useEffect(() => {
        if (completedPlans.length > prevCompletedCount.current) {
            refetch().then(() => {
                completedPlans.forEach((p) => dismissPlan(p.id));
            });
        }
        prevCompletedCount.current = completedPlans.length;
    }, [completedPlans.length, refetch, completedPlans, dismissPlan]);

    const handleDeleteConfirm = async (id: number) => {
        setIsDeleting(true);
        try {
            await deletePlan(id);
            notify.success("Workout plan deleted.");
        } catch {
            notify.error("Failed to delete plan. Please try again.");
        } finally {
            setIsDeleting(false);
            setConfirmDeleteId(null);
        }
    };

    return (
        <div>
            {/* Header */}
            <div className="animate-fade-in-up relative rounded-2xl overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-900 p-6 mb-8">
                {/* Decorative blobs */}
                <div className="absolute top-0 right-0 w-64 h-64 bg-indigo-500/10 rounded-full -translate-y-1/2 translate-x-1/4 pointer-events-none" />
                <div className="absolute bottom-0 left-1/3 w-40 h-40 bg-blue-500/10 rounded-full translate-y-1/2 pointer-events-none" />

                <div className="relative flex flex-col sm:flex-row sm:items-center justify-between gap-5">
                    <div>
                        <div className="flex items-center gap-2 mb-2">
                            <div className="w-8 h-8 rounded-lg bg-indigo-500/20 flex items-center justify-center">
                                <Dumbbell className="w-4 h-4 text-indigo-300" />
                            </div>
                            <span className="text-indigo-300 text-xs font-semibold uppercase tracking-widest">
                                Training Hub
                            </span>
                        </div>
                        <h1 className="text-2xl font-bold text-white mb-1">My Workout Plans</h1>
                        <p className="text-slate-400 text-sm">
                            Your personalized training programs, all in one place
                        </p>
                    </div>

                    <Link to="/dashboard/workout-generate" className="flex-shrink-0">
                        <Button className="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-900/50">
                            <Plus className="w-4 h-4" />
                            New Plan
                        </Button>
                    </Link>
                </div>

                {/* Stats row */}
                {!isLoading && plans.length > 0 && (
                    <div className="relative flex items-center gap-6 mt-5 pt-5 border-t border-white/10">
                        <div className="flex items-center gap-2 text-slate-300">
                            <BarChart3 className="w-4 h-4 text-indigo-400" />
                            <span className="text-sm">
                                <span className="font-semibold text-white">{plans.length}</span>{" "}
                                {plans.length === 1 ? "plan" : "plans"}
                            </span>
                        </div>
                        <div className="flex items-center gap-2 text-slate-300">
                            <Calendar className="w-4 h-4 text-indigo-400" />
                            <span className="text-sm">
                                <span className="font-semibold text-white">
                                    {plans.reduce((acc, p) => acc + p.plan_days.length, 0)}
                                </span>{" "}
                                total sessions
                            </span>
                        </div>
                        <div className="flex items-center gap-2 text-slate-300">
                            <Zap className="w-4 h-4 text-indigo-400" />
                            <span className="text-sm">
                                <span className="font-semibold text-white">
                                    {Math.max(...plans.map((p) => p.training_days_per_week))}
                                </span>{" "}
                                days/week peak
                            </span>
                        </div>
                    </div>
                )}
            </div>

            {/* Generating plan banner */}
            <div className="mb-4">
                <GeneratingWorkoutBanner />
            </div>

            {/* Error state */}
            {error && !isLoading && (
                <div className="text-center py-16">
                    <p className="text-red-500 mb-4">{error}</p>
                    <Button variant="outline" onClick={refetch}>
                        Retry
                    </Button>
                </div>
            )}

            {/* Loading state */}
            {isLoading && (
                <div
                    className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 animate-fade-in-up"
                    style={{ animationDelay: "75ms" }}
                >
                    {Array.from({ length: 6 }).map((_, i) => (
                        <PlanCardSkeleton key={i} />
                    ))}
                </div>
            )}

            {/* Plans grid */}
            {!isLoading && !error && plans.length > 0 && (
                <div
                    className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 animate-fade-in-up"
                    style={{ animationDelay: "75ms" }}
                >
                    {plans.map((plan, index) => {
                        const isConfirming = confirmDeleteId === plan.id;
                        const gradient = GOAL_GRADIENT[plan.goal] ?? "from-slate-500 to-slate-600";
                        const experienceBadge =
                            EXPERIENCE_BADGE[plan.experience_level] ??
                            "bg-slate-100 text-slate-700";

                        const isGenerating = plan.status === "pending" || plan.status === "processing";

                        return (
                            <div
                                key={plan.id}
                                className="bg-white rounded-xl border border-slate-200 overflow-hidden hover:shadow-md hover:border-slate-300 transition-all duration-200 flex flex-col"
                            >
                                {/* Colored top bar */}
                                <div className={`h-1.5 bg-gradient-to-r ${gradient}`} />

                                <div className="p-5 flex flex-col flex-1">
                                    {/* Plan header */}
                                    <div className="flex items-start gap-3 mb-4">
                                        <div
                                            className={`w-10 h-10 rounded-xl bg-gradient-to-br ${gradient} flex items-center justify-center flex-shrink-0 text-lg`}
                                        >
                                            {GOAL_ICON[plan.goal] ?? "🏋️"}
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <div className="flex items-center justify-between gap-2">
                                                <h3 className="font-semibold text-slate-900 text-sm leading-tight truncate">
                                                    {GOAL_LABEL[plan.goal] ?? plan.goal}
                                                </h3>
                                                {isGenerating ? (
                                                    <span className="inline-flex items-center gap-1 text-xs font-medium text-amber-600 bg-amber-50 border border-amber-200 rounded-full px-2 py-0.5 flex-shrink-0 animate-pulse">
                                                        Generating…
                                                    </span>
                                                ) : (
                                                    <span className="text-xs text-slate-400 flex-shrink-0">
                                                        #{index + 1}
                                                    </span>
                                                )}
                                            </div>
                                            <div className="flex items-center gap-2 mt-1">
                                                <span className="text-xs text-slate-500">
                                                    {WORKOUT_TYPE_LABEL[plan.workout_type] ??
                                                        plan.workout_type}
                                                </span>
                                                <span className="text-slate-300">·</span>
                                                <span
                                                    className={`text-xs px-1.5 py-0.5 rounded-full font-medium ${experienceBadge}`}
                                                >
                                                    {EXPERIENCE_LABEL[plan.experience_level] ??
                                                        plan.experience_level}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Stats row */}
                                    <div className="flex items-center gap-3 mb-4">
                                        <div className="flex items-center gap-1.5 text-slate-500">
                                            <Calendar className="w-3.5 h-3.5" />
                                            <span className="text-xs">
                                                {plan.training_days_per_week}d/week
                                            </span>
                                        </div>
                                        <div className="flex items-center gap-1.5 text-slate-500">
                                            <Activity className="w-3.5 h-3.5" />
                                            <span className="text-xs">
                                                {plan.plan_days.length} sessions
                                            </span>
                                        </div>
                                    </div>

                                    {/* Training days */}
                                    <div className="flex flex-wrap gap-1.5 mb-5">
                                        {plan.plan_days.map((day) => (
                                            <div
                                                key={day.id}
                                                className="flex items-center gap-1.5 bg-slate-50 border border-slate-100 rounded-lg px-2.5 py-1.5"
                                                title={day.workout_name ?? undefined}
                                            >
                                                <Dumbbell className="w-3 h-3 text-slate-400" />
                                                <span className="text-xs font-medium text-slate-700">
                                                    {getDayAbbr(day.day_of_week)}
                                                </span>
                                                <div className="flex items-center gap-0.5 text-slate-400">
                                                    <Clock className="w-2.5 h-2.5" />
                                                    <span className="text-xs">
                                                        {day.duration_minutes}m
                                                    </span>
                                                </div>
                                            </div>
                                        ))}
                                    </div>

                                    {/* Actions */}
                                    <div className="mt-auto">
                                        {isConfirming ? (
                                            <div className="flex items-center gap-2">
                                                <p className="text-xs text-slate-500 flex-1">
                                                    Delete this plan?
                                                </p>
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() => setConfirmDeleteId(null)}
                                                    disabled={isDeleting}
                                                    className="text-xs h-8"
                                                >
                                                    Cancel
                                                </Button>
                                                <Button
                                                    variant="destructive"
                                                    size="sm"
                                                    onClick={() => handleDeleteConfirm(plan.id)}
                                                    disabled={isDeleting}
                                                    className="text-xs h-8"
                                                >
                                                    {isDeleting ? "Deleting…" : "Delete"}
                                                </Button>
                                            </div>
                                        ) : isGenerating ? (
                                            <div className="flex items-center gap-2">
                                                <Button
                                                    variant="outline"
                                                    className="flex-1 text-xs h-9"
                                                    disabled
                                                >
                                                    Generating…
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => setConfirmDeleteId(plan.id)}
                                                    className="h-9 w-9 text-red-400 hover:text-red-600 hover:bg-red-50 flex-shrink-0"
                                                    title="Remove stuck plan"
                                                >
                                                    <Trash2 className="w-3.5 h-3.5" />
                                                </Button>
                                            </div>
                                        ) : (
                                            <div className="flex items-center gap-2">
                                                <Link
                                                    to={`/dashboard/workouts/${plan.id}`}
                                                    className="flex-1"
                                                >
                                                    <Button
                                                        variant="outline"
                                                        className="w-full text-xs h-9 justify-between"
                                                    >
                                                        <span>View Plan</span>
                                                        <ChevronRight className="w-3.5 h-3.5" />
                                                    </Button>
                                                </Link>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => setConfirmDeleteId(plan.id)}
                                                    className="h-9 w-9 text-red-400 hover:text-red-600 hover:bg-red-50 flex-shrink-0"
                                                >
                                                    <Trash2 className="w-3.5 h-3.5" />
                                                </Button>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </div>
            )}

            {/* Empty state */}
            {!isLoading && !error && plans.length === 0 && (
                <div
                    className="text-center py-20 animate-fade-in-up"
                    style={{ animationDelay: "75ms" }}
                >
                    <div className="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <TrendingUp className="w-8 h-8 text-slate-400" />
                    </div>
                    <h3 className="text-lg font-semibold text-slate-900 mb-2">
                        No workout plans yet
                    </h3>
                    <p className="text-slate-600 mb-6">
                        Create your first personalized workout plan to get started
                    </p>

                </div>
            )}
        </div>
    );
};
