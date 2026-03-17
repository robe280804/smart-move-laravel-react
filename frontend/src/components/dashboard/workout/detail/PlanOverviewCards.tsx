import { Calendar, Dumbbell, Target } from "lucide-react";
import { Card, CardContent } from "@/components/ui/card";
import { EXPERIENCE_LEVELS } from "@/constants/const";
import type { WorkoutPlan } from "@/types/workout";

const EXPERIENCE_LABEL = Object.fromEntries(
    EXPERIENCE_LEVELS.map((l) => [l, l.charAt(0).toUpperCase() + l.slice(1)]),
);

type Props = {
    plan: WorkoutPlan;
};

export const PlanOverviewCards = ({ plan }: Props) => (
    <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <Card>
            <CardContent className="p-4">
                <div className="flex items-center gap-3">
                    <div className="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <Calendar className="w-5 h-5 text-blue-600" />
                    </div>
                    <div>
                        <p className="text-sm text-slate-600">Training Days</p>
                        <p className="text-xl font-semibold text-slate-900">
                            {plan.training_days_per_week}/week
                        </p>
                    </div>
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardContent className="p-4">
                <div className="flex items-center gap-3">
                    <div className="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <Dumbbell className="w-5 h-5 text-purple-600 " />
                    </div>
                    <div>
                        <p className="text-sm text-slate-600">Total Sessions</p>
                        <p className="text-xl font-semibold text-slate-900">
                            {plan.plan_days.length}
                        </p>
                    </div>
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardContent className="p-4">
                <div className="flex items-center gap-3">
                    <div className="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <Target className="w-5 h-5 text-green-600" />
                    </div>
                    <div>
                        <p className="text-sm text-slate-600">Experience</p>
                        <p className="text-xl font-semibold text-slate-900">
                            {EXPERIENCE_LABEL[plan.experience_level] ?? plan.experience_level}
                        </p>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
);
