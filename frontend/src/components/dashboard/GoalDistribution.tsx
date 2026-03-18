import { Card, CardContent } from "@/components/ui/card";
import { FITNESS_GOALS } from "@/constants/const";
import type { WorkoutPlan } from "@/types/workout";

const GOAL_LABEL = Object.fromEntries(
    FITNESS_GOALS.map((g) => [g.value, g.label]),
);
const GOAL_ICON = Object.fromEntries(
    FITNESS_GOALS.map((g) => [g.value, g.icon]),
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

interface GoalDistributionProps {
    plans: WorkoutPlan[];
}

export function GoalDistribution({ plans }: GoalDistributionProps) {
    const goalCounts: Record<string, number> = {};
    plans.forEach((p) => {
        goalCounts[p.goal] = (goalCounts[p.goal] || 0) + 1;
    });

    const sorted = Object.entries(goalCounts).sort((a, b) => b[1] - a[1]);
    const max = sorted[0]?.[1] || 1;

    if (sorted.length === 0) return null;

    return (
        <Card className="py-0">
            <CardContent className="p-5">
                <h3 className="text-sm font-semibold text-slate-900 mb-4">
                    Goals Breakdown
                </h3>
                <div className="space-y-3">
                    {sorted.map(([goal, count]) => (
                        <div key={goal}>
                            <div className="flex items-center justify-between text-xs mb-1.5">
                                <div className="flex items-center gap-1.5">
                                    <span>{GOAL_ICON[goal] ?? "🏋️"}</span>
                                    <span className="text-slate-700 font-medium">
                                        {GOAL_LABEL[goal] ?? goal}
                                    </span>
                                </div>
                                <span className="text-slate-400 tabular-nums">
                                    {count} {count === 1 ? "plan" : "plans"}
                                </span>
                            </div>
                            <div className="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div
                                    className={`h-full rounded-full bg-gradient-to-r ${
                                        GOAL_GRADIENT[goal] ??
                                        "from-slate-400 to-slate-500"
                                    } transition-all duration-700 ease-out`}
                                    style={{
                                        width: `${(count / max) * 100}%`,
                                    }}
                                />
                            </div>
                        </div>
                    ))}
                </div>
            </CardContent>
        </Card>
    );
}
