import { Check } from "lucide-react";
import { Card, CardContent } from "@/components/ui/card";

const STEP_LABELS = [
    "Goal",
    "Schedule",
    "Constraints",
    "Equipment",
    "Preferences",
    "Details",
    "Generate",
];

interface WorkoutProgressBarProps {
    step: number;
}

export function WorkoutProgressBar({ step }: WorkoutProgressBarProps) {
    return (
        <Card className="bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 border-slate-700">
            <CardContent className="p-4">
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