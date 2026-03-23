import { Dumbbell, Link as LinkIcon } from "lucide-react";
import { Link } from "react-router";
import { Card, CardContent } from "@/components/ui/card";
import type { WorkoutPlan } from "@/types/workout";
import { FITNESS_GOALS } from "@/constants/const";

const DAY_LABELS = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"] as const;

const GOAL_LABEL = Object.fromEntries(FITNESS_GOALS.map((g) => [g.value, g.label]));

// day_of_week from backend: 1 = Monday … 7 = Sunday
// JS getDay():             0 = Sunday … 6 = Saturday
const getTodayIndex = (): number => {
    const jsDay = new Date().getDay();
    return jsDay === 0 ? 6 : jsDay - 1; // 0 = Mon … 6 = Sun
};

interface WeeklyOverviewProps {
    plans: WorkoutPlan[];
}

interface DaySlot {
    duration: number;
    name: string | null;
}

export function WeeklyOverview({ plans }: WeeklyOverviewProps) {
    const activePlan = [...plans]
        .filter((p) => p.status === "completed" && p.plan_days.length > 0)
        .sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())[0] ?? null;

    const schedule = new Array<DaySlot | null>(7).fill(null);
    if (activePlan) {
        activePlan.plan_days.forEach((day) => {
            const index = day.day_of_week - 1;
            if (index >= 0 && index < 7) {
                schedule[index] = {
                    duration: day.duration_minutes,
                    name: day.workout_name,
                };
            }
        });
    }

    const todayIndex = getTodayIndex();

    return (
        <Card className="py-0">
            <CardContent className="p-5">
                {/* Header */}
                <div className="flex items-center justify-between mb-4">
                    <h3 className="text-sm font-semibold text-slate-900">
                        Weekly Schedule
                    </h3>
                    {activePlan && (
                        <Link
                            to={`/dashboard/workouts/${activePlan.id}`}
                            className="flex items-center gap-1 text-[10px] text-indigo-500 hover:text-indigo-700 font-medium transition-colors truncate max-w-[110px]"
                        >
                            <span className="truncate">
                                {GOAL_LABEL[activePlan.goal] ?? activePlan.goal}
                            </span>
                            <LinkIcon className="w-2.5 h-2.5 flex-shrink-0" />
                        </Link>
                    )}
                </div>

                {activePlan ? (
                    <>
                        <div className="flex gap-1.5">
                            {DAY_LABELS.map((day, i) => {
                                const slot = schedule[i];
                                const isToday = i === todayIndex;
                                const isTraining = slot !== null;

                                return (
                                    <div
                                        key={day}
                                        className="flex-1 flex flex-col items-center gap-1"
                                    >
                                        {/* Indicator pill */}
                                        <div
                                            className={`w-full h-9 rounded-lg flex items-center justify-center transition-all ${
                                                isTraining && isToday
                                                    ? "bg-gradient-to-b from-indigo-500 to-indigo-700 ring-2 ring-indigo-300 ring-offset-1 shadow-sm shadow-indigo-500/30"
                                                    : isTraining
                                                        ? "bg-gradient-to-b from-indigo-500 to-indigo-700 shadow-sm shadow-indigo-500/20"
                                                        : isToday
                                                            ? "bg-slate-200 ring-2 ring-slate-300 ring-offset-1"
                                                            : "bg-slate-100"
                                            }`}
                                        >
                                            {isTraining ? (
                                                <Dumbbell className="w-3.5 h-3.5 text-white" />
                                            ) : (
                                                <span className="w-2 h-0.5 rounded-full bg-slate-300 block" />
                                            )}
                                        </div>

                                        {/* Duration / rest */}
                                        <span
                                            className={`text-[9px] font-semibold leading-none ${
                                                isTraining ? "text-indigo-600" : "text-slate-300"
                                            }`}
                                        >
                                            {isTraining ? `${slot.duration}m` : "·"}
                                        </span>

                                        {/* Day name */}
                                        <span
                                            className={`text-[10px] font-medium leading-none ${
                                                isToday
                                                    ? "text-indigo-600"
                                                    : isTraining
                                                        ? "text-slate-600"
                                                        : "text-slate-400"
                                            }`}
                                        >
                                            {day}
                                        </span>

                                        {/* "Today" dot */}
                                        {isToday && (
                                            <span className="w-1 h-1 rounded-full bg-indigo-500 block" />
                                        )}
                                    </div>
                                );
                            })}
                        </div>

                        {/* Summary row */}
                        <div className="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                            <span className="text-[10px] text-slate-400">
                                {activePlan.training_days_per_week} training day{activePlan.training_days_per_week !== 1 ? "s" : ""} / week
                            </span>
                            {(() => {
                                const todaySlot = schedule[todayIndex];
                                return todaySlot ? (
                                    <span className="text-[10px] font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">
                                        Train today · {todaySlot.duration}min
                                    </span>
                                ) : (
                                    <span className="text-[10px] text-slate-400 bg-slate-50 px-2 py-0.5 rounded-full">
                                        Rest day today
                                    </span>
                                );
                            })()}
                        </div>
                    </>
                ) : (
                    <div className="flex flex-col items-center justify-center py-5 text-center gap-2">
                        <div className="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center">
                            <Dumbbell className="w-4 h-4 text-slate-300" />
                        </div>
                        <p className="text-xs text-slate-400 max-w-[160px]">
                            Generate a plan to see your weekly schedule here.
                        </p>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
