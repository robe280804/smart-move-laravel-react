import { Link } from "react-router";
import {
    Dumbbell,
    Sparkles,
    ChevronRight,
    Calendar,
    BarChart3,
    Zap,
    Crown,
    ArrowUpRight,
} from "lucide-react";
import { Card, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { useAuth } from "@/contexts/AuthContext";
import { useWorkoutPlans } from "@/hooks/useWorkoutPlans";
import { useSubscription } from "@/hooks/useSubscription";
import { FITNESS_GOALS, WORKOUT_TYPES } from "@/constants/const";

const GOAL_LABEL = Object.fromEntries(FITNESS_GOALS.map((g) => [g.value, g.label]));
const GOAL_ICON = Object.fromEntries(FITNESS_GOALS.map((g) => [g.value, g.icon]));
const WORKOUT_TYPE_LABEL = Object.fromEntries(WORKOUT_TYPES.map((w) => [w.value, w.label]));

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

const getGreeting = () => {
    const h = new Date().getHours();
    if (h < 12) return "Good morning";
    if (h < 17) return "Good afternoon";
    if (h < 21) return "Good evening";
    return "Good night";
};

const PLAN_LABEL: Record<string, string> = {
    free: "Free",
    advanced: "Advanced",
    pro: "Pro",
};

export const Dashboard = () => {
    const { user } = useAuth();
    const { plans, isLoading: plansLoading } = useWorkoutPlans();
    const { currentPlan } = useSubscription();

    const totalSessions = plans.reduce((acc, p) => acc + p.plan_days.length, 0);
    const recentPlans = [...plans]
        .sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
        .slice(0, 3);

    const isOnFreePlan = currentPlan === "free";

    return (
        <div className=" mx-auto space-y-6">
            {/* Hero greeting */}
            <div className="relative rounded-2xl overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-900 p-6">
                <div className="absolute top-0 right-0 w-64 h-64 bg-indigo-500/10 rounded-full -translate-y-1/2 translate-x-1/4 pointer-events-none" />
                <div className="absolute bottom-0 left-1/3 w-40 h-40 bg-blue-500/10 rounded-full translate-y-1/2 pointer-events-none" />
                <div className="relative">
                    <p className="text-indigo-300 text-sm font-medium mb-1">{getGreeting()}</p>
                    <h1 className="text-2xl font-bold text-white mb-1">
                        {user?.name} {user?.surname}
                    </h1>
                    <p className="text-slate-400 text-sm">
                        {new Date().toLocaleDateString("en-US", {
                            weekday: "long",
                            month: "long",
                            day: "numeric",
                        })}
                    </p>
                </div>
            </div>

            {/* Stats row */}
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <Card>
                    <CardContent className="py-3 flex items-center gap-4">
                        <div className="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <BarChart3 className="w-5 h-5 text-indigo-600" />
                        </div>
                        <div>
                            <p className="text-2xl font-bold text-slate-900">
                                {plansLoading ? "—" : plans.length}
                            </p>
                            <p className="text-xs text-slate-500">Plans created</p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="py-3 flex items-center gap-4">
                        <div className="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <Calendar className="w-5 h-5 text-blue-600" />
                        </div>
                        <div>
                            <p className="text-2xl font-bold text-slate-900">
                                {plansLoading ? "—" : totalSessions}
                            </p>
                            <p className="text-xs text-slate-500">Total sessions</p>
                        </div>
                    </CardContent>
                </Card>

                <Link to="/dashboard/profile?tab=subscription" className="group block">
                    <Card className="hover:shadow-md transition-shadow cursor-pointer h-full">
                        <CardContent className="py-3 flex items-center gap-4">
                            <div className="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center flex-shrink-0">
                                <Zap className="w-5 h-5 text-purple-600" />
                            </div>
                            <div className="flex-1 min-w-0">
                                <p className="text-sm font-bold text-slate-900 capitalize">
                                    {currentPlan ? PLAN_LABEL[currentPlan] : "—"}
                                </p>
                                <p className="text-xs text-slate-500">Current plan</p>
                            </div>
                            <ChevronRight className="w-4 h-4 text-slate-400 group-hover:text-indigo-500 transition-colors flex-shrink-0" />
                        </CardContent>
                    </Card>
                </Link>
            </div>

            {/* Quick actions */}
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <Link to="/dashboard/workout-generate">
                    <div className="group relative rounded-xl overflow-hidden bg-gradient-to-br from-indigo-600 to-blue-700 p-5 cursor-pointer hover:shadow-lg hover:shadow-indigo-900/30 transition-all">
                        <div className="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/4 pointer-events-none" />
                        <div className="relative">
                            <div className="w-10 h-10 bg-white/15 rounded-xl flex items-center justify-center mb-3">
                                <Sparkles className="w-5 h-5 text-white" />
                            </div>
                            <h3 className="text-base font-semibold text-white mb-1">
                                Generate Workout
                            </h3>
                            <p className="text-sm text-indigo-200 mb-3">
                                Let AI create a personalized plan for your goals
                            </p>
                            <div className="flex items-center gap-1 text-white text-xs font-medium">
                                <span>Get started</span>
                                <ChevronRight className="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" />
                            </div>
                        </div>
                    </div>
                </Link>

                <Link to="/dashboard/workouts">
                    <div className="group rounded-xl border border-slate-200 bg-white p-5 cursor-pointer hover:shadow-md transition-shadow">
                        <div className="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center mb-3">
                            <Dumbbell className="w-5 h-5 text-slate-700" />
                        </div>
                        <h3 className="text-base font-semibold text-slate-900 mb-1">
                            My Workouts
                        </h3>
                        <p className="text-sm text-slate-500 mb-3">
                            Browse and manage all your workout plans
                        </p>
                        <div className="flex items-center gap-1 text-indigo-600 text-xs font-medium">
                            <span>View all</span>
                            <ChevronRight className="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" />
                        </div>
                    </div>
                </Link>
            </div>

            {/* Recent plans */}
            {!plansLoading && recentPlans.length > 0 && (
                <div>
                    <div className="flex items-center justify-between mb-3">
                        <h2 className="text-sm font-semibold text-slate-900">Recent Plans</h2>
                        <Link
                            to="/dashboard/workouts"
                            className="text-xs text-indigo-600 hover:underline flex items-center gap-1"
                        >
                            See all <ArrowUpRight className="w-3 h-3" />
                        </Link>
                    </div>

                    <div className="space-y-2">
                        {recentPlans.map((plan) => {
                            const gradient =
                                GOAL_GRADIENT[plan.goal] ?? "from-slate-500 to-slate-600";
                            return (
                                <Link key={plan.id} to={`/dashboard/workouts/${plan.id}`}>
                                    <div className="flex items-center gap-3 bg-white border border-slate-200 rounded-xl p-3.5 hover:shadow-sm transition-shadow cursor-pointer">
                                        <div
                                            className={`w-9 h-9 rounded-lg bg-gradient-to-br ${gradient} flex items-center justify-center text-base flex-shrink-0`}
                                        >
                                            {GOAL_ICON[plan.goal] ?? "🏋️"}
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <p className="text-sm font-medium text-slate-900 truncate">
                                                {GOAL_LABEL[plan.goal] ?? plan.goal}
                                            </p>
                                            <p className="text-xs text-slate-500">
                                                {WORKOUT_TYPE_LABEL[plan.workout_type] ??
                                                    plan.workout_type}{" "}
                                                · {plan.training_days_per_week}d/week
                                            </p>
                                        </div>
                                        <div className="flex items-center gap-2 flex-shrink-0">
                                            <span className="text-xs text-slate-400">
                                                {plan.plan_days.length}{" "}
                                                {plan.plan_days.length === 1
                                                    ? "session"
                                                    : "sessions"}
                                            </span>
                                            <ChevronRight className="w-4 h-4 text-slate-400" />
                                        </div>
                                    </div>
                                </Link>
                            );
                        })}
                    </div>
                </div>
            )}

            {/* Empty state */}
            {!plansLoading && plans.length === 0 && (
                <Card>
                    <CardContent className="py-10 flex flex-col items-center text-center">
                        <div className="w-14 h-14 bg-indigo-50 rounded-full flex items-center justify-center mb-4">
                            <Dumbbell className="w-7 h-7 text-indigo-400" />
                        </div>
                        <h3 className="text-base font-semibold text-slate-900 mb-1">
                            No workout plans yet
                        </h3>
                        <p className="text-sm text-slate-500 mb-4">
                            Create your first AI-powered plan to get started
                        </p>
                        <Link to="/dashboard/workout-generate">
                            <Button
                                size="sm"
                                className="bg-indigo-600 hover:bg-indigo-500 text-white"
                            >
                                <Sparkles className="w-4 h-4 mr-1.5" />
                                Generate your first plan
                            </Button>
                        </Link>
                    </CardContent>
                </Card>
            )}

            {/* Upgrade banner — only shown on free plan */}
            {isOnFreePlan && (
                <div className="rounded-xl bg-gradient-to-r from-indigo-600 to-blue-700 p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <div className="flex items-center gap-2 mb-1">
                            <Crown className="w-4 h-4 text-yellow-300" />
                            <p className="text-sm font-semibold text-white">Upgrade to Advanced</p>
                        </div>
                        <p className="text-xs text-indigo-200">
                            Unlock PDF export, exercise editing, full plan history and more.
                        </p>
                    </div>
                    <Link to="/dashboard/profile">
                        <Button
                            size="sm"
                            className="bg-white text-indigo-600 hover:bg-indigo-50 flex-shrink-0"
                        >
                            View plans
                        </Button>
                    </Link>
                </div>
            )}
        </div>
    );
};
