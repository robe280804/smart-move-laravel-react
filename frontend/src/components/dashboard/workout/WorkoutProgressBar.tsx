import { Check, Sparkles } from "lucide-react";
import { Card, CardContent } from "@/components/ui/card";

const STEP_LABELS = [
    "Goal",
    "Schedule",
    "Constraints",
    "Equipment",
    "Details",
    "Generate",
];

interface WorkoutProgressBarProps {
    step: number;
}

export function WorkoutProgressBar({ step }: WorkoutProgressBarProps) {
    return (
        <Card className="bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-900 border-slate-700 overflow-hidden relative">
            {/* Decorative blobs */}
            <div className="absolute top-0 right-0 w-64 h-64 bg-indigo-500/10 rounded-full -translate-y-1/2 translate-x-1/4 pointer-events-none" />
            <div className="absolute bottom-0 left-1/3 w-40 h-40 bg-blue-500/10 rounded-full translate-y-1/2 pointer-events-none" />

            <CardContent className="relative p-6">
                {/* Title + description */}
                <div className="mb-6">
                    <div className="flex items-center gap-2 mb-2">
                        <div className="w-8 h-8 rounded-lg bg-indigo-500/20 flex items-center justify-center">
                            <Sparkles className="w-4 h-4 text-indigo-300" />
                        </div>
                        <span className="text-indigo-300 text-xs font-semibold uppercase tracking-widest">
                            AI Powered
                        </span>
                    </div>
                    <h1 className="text-2xl font-bold text-white mb-1">AI Workout Plan Generator</h1>
                    <p className="text-slate-400 text-sm">Create a personalized workout plan with AI assistance</p>
                </div>

                {/* Steps */}
                <div className="flex items-center justify-between">
                    {STEP_LABELS.map((label, index) => (
                        <div key={index} className="flex items-center">

                            <div className="flex flex-col items-center">
                                <div
                                    className={`w-10 h-10 rounded-full flex items-center justify-center font-semibold transition-all
                    ${index < step
                                            ? "bg-gradient-to-r from-blue-500 to-indigo-500 text-white"
                                            : index === step
                                                ? "bg-gradient-to-r from-blue-500 to-indigo-500 text-white ring-2 ring-indigo-300"
                                                : "bg-slate-700 text-slate-300"
                                        }`}
                                >
                                    {index < step ? <Check className="w-5 h-5" /> : index + 1}
                                </div>

                                <span
                                    className={`text-xs mt-2 font-medium ${index <= step ? "text-slate-200" : "text-slate-500"
                                        }`}
                                >
                                    {label}
                                </span>
                            </div>

                            {index < STEP_LABELS.length - 1 && (
                                <div
                                    className={`h-0.5 w-12 mx-2 transition-all ${index < step
                                        ? "bg-gradient-to-r from-blue-500 to-indigo-500"
                                        : "bg-slate-700"
                                        }`}
                                />
                            )}
                        </div>
                    ))}
                </div>
            </CardContent>
        </Card>
    );
}
