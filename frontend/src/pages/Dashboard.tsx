import { Link } from "react-router";
import {
    Dumbbell,
    Sparkles,
    ChevronRight,
    Calendar,
    BarChart3,
    Zap,
    Crown,
    Timer,
} from "lucide-react";
import { Card, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { useAuth } from "@/contexts/AuthContext";
import { useWorkoutPlans } from "@/hooks/useWorkoutPlans";
import { useSubscription } from "@/hooks/useSubscription";
import { AdminDashboard } from "@/pages/admin/AdminDashboard";
import { FITNESS_GOALS, WORKOUT_TYPES } from "@/constants/const";
import { StatCard } from "@/components/dashboard/StatCard";
import { GoalDistribution } from "@/components/dashboard/GoalDistribution";
import { WeeklyOverview } from "@/components/dashboard/WeeklyOverview";
import { DashboardSkeleton } from "@/components/dashboard/DashboardSkeleton";

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

const PLAN_LABEL: Record<string, string> = {
    free: "Free",
    advanced: "Advanced",
    pro: "Pro",
};

const getGreeting = () => {
    const h = new Date().getHours();
    if (h < 12) return "Good morning";
    if (h < 17) return "Good afternoon";
    if (h < 21) return "Good evening";
    return "Good night";
};

const formatDuration = (minutes: number) => {
    if (minutes === 0) return "0m";
    const h = Math.floor(minutes / 60);
    const m = minutes % 60;
    if (h === 0) return `${m}m`;
    return m > 0 ? `${h}h ${m}m` : `${h}h`;
};

const getRelativeTime = (dateStr: string) => {
    const diff = Math.floor(
        (Date.now() - new Date(dateStr).getTime()) / (1000 * 60 * 60 * 24),
    );
    if (diff === 0) return "Today";
    if (diff === 1) return "Yesterday";
    if (diff < 7) return `${diff}d ago`;
    if (diff < 30) return `${Math.floor(diff / 7)}w ago`;
    return `${Math.floor(diff / 30)}mo ago`;
};

export const Dashboard = () => {
    const { user, isAdmin } = useAuth();
    const { plans, isLoading: plansLoading } = useWorkoutPlans();
    const { currentPlan } = useSubscription();

    if (isAdmin) return <AdminDashboard />;

    if (plansLoading) return <DashboardSkeleton />;

    const totalSessions = plans.reduce((acc, p) => acc + p.plan_days.length, 0);
    const totalMinutes = plans.reduce(
        (acc, p) =>
            acc + p.plan_days.reduce((sum, d) => sum + d.duration_minutes, 0),
        0,
    );
    const recentPlans = [...plans]
        .sort(
            (a, b) =>
                new Date(b.created_at).getTime() -
                new Date(a.created_at).getTime(),
        )
        .slice(0, 5);

    const isOnFreePlan = currentPlan === "free";

    return (
        <div className="mx-auto space-y-6">
            {/* ── Greeting ─────────────────────────── */}
            <section className="animate-fade-in-up relative rounded-2xl overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-900 p-6 sm:p-8">
                <div className="absolute top-0 right-0 w-64 h-64 bg-indigo-500/10 rounded-full -translate-y-1/2 translate-x-1/4 pointer-events-none" />
                <div className="absolute bottom-0 left-1/3 w-40 h-40 bg-blue-500/10 rounded-full translate-y-1/2 pointer-events-none" />

                <div className="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <p className="text-indigo-300 text-sm font-medium mb-1">
                            {getGreeting()}
                        </p>
                        <h1 className="text-2xl sm:text-3xl font-bold text-white mb-1">
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
                    <Link
                        to="/dashboard/workout-generate"
                        className="flex-shrink-0"
                    >
                        <Button
                            size="sm"
                            className="bg-indigo-500 hover:bg-indigo-400 text-white shadow-lg shadow-indigo-500/25"
                        >
                            <Sparkles className="w-4 h-4 mr-1.5" />
                            New Workout
                        </Button>
                    </Link>
                </div>
            </section>

            {/* ── KPI Stats ────────────────────────── */}
            <section
                className="grid grid-cols-2 lg:grid-cols-4 gap-4 animate-fade-in-up"
                style={{ animationDelay: "75ms" }}
            >
                <StatCard
                    icon={<BarChart3 className="w-5 h-5 text-indigo-600" />}
                    iconBg="bg-indigo-50"
                    label="Plans created"
                    value={plans.length}
                />
                <StatCard
                    icon={<Calendar className="w-5 h-5 text-blue-600" />}
                    iconBg="bg-blue-50"
                    label="Total sessions"
                    value={totalSessions}
                />
                <StatCard
                    icon={<Timer className="w-5 h-5 text-emerald-600" />}
                    iconBg="bg-emerald-50"
                    label="Total duration"
                    value={formatDuration(totalMinutes)}
                />
                <StatCard
                    icon={<Zap className="w-5 h-5 text-purple-600" />}
                    iconBg="bg-purple-50"
                    label="Current plan"
                    value={currentPlan ? PLAN_LABEL[currentPlan] : "—"}
                    link="/dashboard/profile?tab=subscription"
                />
            </section>

            {/* ── Quick Actions ────────────────────── */}
            <section
                className="grid grid-cols-1 sm:grid-cols-2 gap-4 animate-fade-in-up"
                style={{ animationDelay: "150ms" }}
            >
                <Link to="/dashboard/workout-generate">
                    <div className="group relative rounded-xl overflow-hidden bg-gradient-to-br from-indigo-600 to-blue-700 p-5 cursor-pointer hover:shadow-lg hover:shadow-indigo-900/30 transition-all duration-200 h-full">
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
                    <div className="group rounded-xl border border-slate-200 bg-white p-5 cursor-pointer hover:shadow-md hover:border-slate-300 transition-all duration-200 h-full">
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
            </section>

            {/* ── Analytics + Recent Plans ─────────── */}
            {plans.length > 0 && (
                <section
                    className="grid grid-cols-1 lg:grid-cols-5 gap-6 animate-fade-in-up"
                    style={{ animationDelay: "225ms" }}
                >
                    {/* Recent Plans */}
                    <div className="lg:col-span-3">
                        <div className="flex items-center justify-between mb-3">
                            <h2 className="text-sm font-semibold text-slate-900">
                                Recent Plans
                            </h2>
                        </div>

                        <div className="space-y-2">
                            {recentPlans.map((plan) => {
                                const gradient =
                                    GOAL_GRADIENT[plan.goal] ??
                                    "from-slate-500 to-slate-600";
                                return (
                                    <Link
                                        key={plan.id}
                                        to={`/dashboard/workouts/${plan.id}`}
                                    >
                                        <div className="flex items-center gap-3 bg-white border border-slate-200 rounded-xl p-3.5 hover:shadow-sm hover:border-slate-300 transition-all duration-200 cursor-pointer">
                                            <div
                                                className={`w-9 h-9 rounded-lg bg-gradient-to-br ${gradient} flex items-center justify-center text-base flex-shrink-0`}
                                            >
                                                {GOAL_ICON[plan.goal] ??
                                                    "🏋️"}
                                            </div>
                                            <div className="flex-1 min-w-0">
                                                <p className="text-sm font-medium text-slate-900 truncate">
                                                    {GOAL_LABEL[plan.goal] ??
                                                        plan.goal}
                                                </p>
                                                <p className="text-xs text-slate-500">
                                                    {WORKOUT_TYPE_LABEL[
                                                        plan.workout_type
                                                    ] ?? plan.workout_type}{" "}
                                                    ·{" "}
                                                    {
                                                        plan.training_days_per_week
                                                    }
                                                    d/week
                                                </p>
                                            </div>
                                            <div className="flex items-center gap-3 flex-shrink-0">
                                                <span className="text-xs text-slate-400 hidden sm:inline">
                                                    {getRelativeTime(
                                                        plan.created_at,
                                                    )}
                                                </span>
                                                <span className="text-xs text-slate-400">
                                                    {plan.plan_days.length}{" "}
                                                    {plan.plan_days.length === 1
                                                        ? "session"
                                                        : "sessions"}
                                                </span>
                                                <ChevronRight className="w-4 h-4 text-slate-300" />
                                            </div>
                                        </div>
                                    </Link>
                                );
                            })}
                        </div>
                    </div>

                    {/* Analytics sidebar */}
                    <div className="lg:col-span-2 space-y-4">
                        <GoalDistribution plans={plans} />
                        <WeeklyOverview plans={plans} />
                    </div>
                </section>
            )}

            {/* ── Empty state ──────────────────────── */}
            {plans.length === 0 && (
                <section
                    className="animate-fade-in-up"
                    style={{ animationDelay: "225ms" }}
                >
                    <Card className="py-0">
                        <CardContent className="py-12 flex flex-col items-center text-center">
                            <div className="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center mb-5">
                                <Dumbbell className="w-8 h-8 text-indigo-400" />
                            </div>
                            <h3 className="text-lg font-semibold text-slate-900 mb-2">
                                No workout plans yet
                            </h3>
                            <p className="text-sm text-slate-500 mb-5 max-w-sm">
                                Create your first AI-powered workout plan to
                                start tracking your fitness journey
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
                </section>
            )}

            {/* ── Upgrade banner ───────────────────── */}
            {isOnFreePlan && (
                <section
                    className="animate-fade-in-up"
                    style={{ animationDelay: "300ms" }}
                >
                    <div className="rounded-xl bg-gradient-to-r from-indigo-600 to-blue-700 p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <div className="flex items-center gap-2 mb-1">
                                <Crown className="w-4 h-4 text-yellow-300" />
                                <p className="text-sm font-semibold text-white">
                                    Upgrade to Advanced
                                </p>
                            </div>
                            <p className="text-xs text-indigo-200">
                                Unlock PDF export, exercise editing, full plan
                                history and more.
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
                </section>
            )}
        </div>
    );
};
