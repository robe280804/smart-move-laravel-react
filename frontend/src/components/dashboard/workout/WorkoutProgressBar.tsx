import { Check, Sparkles } from "lucide-react";
import { Card, CardContent } from "@/components/ui/card";
import { WORKOUT_STEPS } from "@/constants/const";

interface WorkoutProgressBarProps {
    step: number;
}

export function WorkoutProgressBar({ step }: WorkoutProgressBarProps) {
    const totalSteps = WORKOUT_STEPS.length;
    const currentLabel = step < totalSteps ? WORKOUT_STEPS[step].label : "Complete";
    const progressPercent = Math.min((step / (totalSteps - 1)) * 100, 100);

    return (
        <Card className="bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-900 border-slate-700 overflow-hidden relative">
            <div className="absolute top-0 right-0 w-64 h-64 bg-indigo-500/10 rounded-full -translate-y-1/2 translate-x-1/4 pointer-events-none" />
            <div className="absolute bottom-0 left-1/3 w-40 h-40 bg-blue-500/10 rounded-full translate-y-1/2 pointer-events-none" />

            <CardContent className="relative p-5 sm:p-6">
                <div className="mb-5 sm:mb-6">
                    <div className="flex items-center gap-2 mb-2">
                        <div className="w-8 h-8 rounded-lg bg-indigo-500/20 flex items-center justify-center">
                            <Sparkles className="w-4 h-4 text-indigo-300" />
                        </div>
                        <span className="text-indigo-300 text-xs font-semibold uppercase tracking-widest">
                            AI Powered
                        </span>
                    </div>
                    <h1 className="text-xl sm:text-2xl font-bold text-white mb-1">AI Workout Plan Generator</h1>
                    <p className="text-slate-400 text-sm">Create a personalized workout plan with AI assistance</p>
                </div>

                {/* Desktop: full step indicators */}
                <div className="hidden sm:flex items-center justify-between">
                    {WORKOUT_STEPS.map((s, index) => (
                        <div key={index} className="flex items-center">
                            <div className="flex flex-col items-center">
                                <div
                                    className={`w-9 h-9 rounded-full flex items-center justify-center font-semibold text-sm transition-all
                                        ${index < step
                                            ? "bg-gradient-to-r from-blue-500 to-indigo-500 text-white"
                                            : index === step
                                                ? "bg-gradient-to-r from-blue-500 to-indigo-500 text-white ring-2 ring-indigo-300 ring-offset-1 ring-offset-slate-800"
                                                : "bg-slate-700 text-slate-400"
                                        }`}
                                >
                                    {index < step ? <Check className="w-4 h-4" /> : index + 1}
                                </div>
                                <span
                                    className={`text-xs mt-1.5 font-medium ${index <= step ? "text-slate-200" : "text-slate-500"}`}
                                >
                                    {s.label}
                                </span>
                            </div>
                            {index < WORKOUT_STEPS.length - 1 && (
                                <div
                                    className={`h-0.5 w-8 lg:w-12 mx-1 lg:mx-2 transition-all ${index < step
                                        ? "bg-gradient-to-r from-blue-500 to-indigo-500"
                                        : "bg-slate-700"
                                        }`}
                                />
                            )}
                        </div>
                    ))}
                </div>

                {/* Mobile: compact step counter + progress bar */}
                <div className="sm:hidden">
                    <div className="flex items-center justify-between mb-2">
                        <span className="text-sm font-semibold text-slate-200">{currentLabel}</span>
                        <span className="text-xs text-slate-400">
                            Step {Math.min(step + 1, totalSteps)} of {totalSteps}
                        </span>
                    </div>
                    <div className="w-full h-1.5 bg-slate-700 rounded-full overflow-hidden">
                        <div
                            className="h-full bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full transition-all duration-500"
                            style={{ width: `${progressPercent}%` }}
                        />
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}
