import { Link } from "react-router";
import { Loader2, CheckCircle2, XCircle, ChevronRight, X } from "lucide-react";
import { Button } from "@/components/ui/button";
import { useGeneratingPlans } from "@/hooks/useGeneratingPlans";

export function GeneratingWorkoutBanner() {
    const { activePlans, completedPlans, failedPlans, dismissPlan } =
        useGeneratingPlans();

    if (
        activePlans.length === 0 &&
        completedPlans.length === 0 &&
        failedPlans.length === 0
    ) {
        return null;
    }

    return (
        <div className="space-y-2">
            {activePlans.map((plan) => (
                <div
                    key={plan.id}
                    className="flex items-center gap-3 rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-3 animate-fade-in-up"
                >
                    <Loader2 className="w-5 h-5 text-indigo-600 animate-spin flex-shrink-0" />
                    <div className="flex-1 min-w-0">
                        <p className="text-sm font-medium text-indigo-900">
                            Your workout plan is being generated...
                        </p>
                        <p className="text-xs text-indigo-600">
                            This usually takes 1-2 minutes. You can navigate
                            freely.
                        </p>
                    </div>
                </div>
            ))}

            {completedPlans.map((plan) => (
                <div
                    key={plan.id}
                    className="flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 animate-fade-in-up"
                >
                    <CheckCircle2 className="w-5 h-5 text-green-600 flex-shrink-0" />
                    <div className="flex-1 min-w-0">
                        <p className="text-sm font-medium text-green-900">
                            Your workout plan is ready!
                        </p>
                    </div>
                    <div className="flex items-center gap-1.5 flex-shrink-0">
                        <Link to={`/dashboard/workouts/${plan.id}`}>
                            <Button
                                size="sm"
                                className="bg-green-600 hover:bg-green-500 text-white text-xs h-8"
                            >
                                View Plan
                                <ChevronRight className="w-3.5 h-3.5 ml-1" />
                            </Button>
                        </Link>
                        <button
                            onClick={() => dismissPlan(plan.id)}
                            className="p-1 rounded-md text-green-400 hover:text-green-600 hover:bg-green-100 transition-colors"
                        >
                            <X className="w-4 h-4" />
                        </button>
                    </div>
                </div>
            ))}

            {failedPlans.map((plan) => (
                <div
                    key={plan.id}
                    className="flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 animate-fade-in-up"
                >
                    <XCircle className="w-5 h-5 text-red-500 flex-shrink-0" />
                    <div className="flex-1 min-w-0">
                        <p className="text-sm font-medium text-red-900">
                            Workout plan generation failed.
                        </p>
                        <p className="text-xs text-red-600">
                            Please try generating a new plan.
                        </p>
                    </div>
                    <button
                        onClick={() => dismissPlan(plan.id)}
                        className="p-1 rounded-md text-red-400 hover:text-red-600 hover:bg-red-100 transition-colors flex-shrink-0"
                    >
                        <X className="w-4 h-4" />
                    </button>
                </div>
            ))}
        </div>
    );
}
