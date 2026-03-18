import { Card, CardContent } from "@/components/ui/card";
import type { WorkoutPlan } from "@/types/workout";

const DAYS = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];

interface WeeklyOverviewProps {
    plans: WorkoutPlan[];
}

export function WeeklyOverview({ plans }: WeeklyOverviewProps) {
    const dayActivity = new Array(7).fill(0) as number[];
    plans.forEach((plan) => {
        plan.plan_days.forEach((day) => {
            dayActivity[day.day_of_week - 1]++;
        });
    });

    const max = Math.max(...dayActivity, 1);

    return (
        <Card className="py-0">
            <CardContent className="p-5">
                <h3 className="text-sm font-semibold text-slate-900 mb-4">
                    Weekly Schedule
                </h3>
                <div className="flex items-end gap-2 h-28">
                    {DAYS.map((day, i) => (
                        <div
                            key={day}
                            className="flex-1 flex flex-col items-center gap-1.5"
                        >
                            <div className="w-full flex-1 flex items-end">
                                <div
                                    className={`w-full rounded-t-md transition-all duration-500 ease-out ${
                                        dayActivity[i] > 0
                                            ? "bg-gradient-to-t from-indigo-600 to-indigo-400"
                                            : "bg-slate-100"
                                    }`}
                                    style={{
                                        height:
                                            dayActivity[i] > 0
                                                ? `${Math.max((dayActivity[i] / max) * 100, 18)}%`
                                                : "8%",
                                    }}
                                />
                            </div>
                            <span
                                className={`text-[10px] font-medium ${
                                    dayActivity[i] > 0
                                        ? "text-slate-600"
                                        : "text-slate-400"
                                }`}
                            >
                                {day}
                            </span>
                        </div>
                    ))}
                </div>
            </CardContent>
        </Card>
    );
}
