import { useEffect, useState } from "react";
import { Sparkles } from "lucide-react";

const GENERATING_MESSAGES = [
    "Analyzing your fitness profile...",
    "Building your weekly structure...",
    "Selecting exercises for your goals...",
    "Optimizing rest and recovery periods...",
    "Finalizing your personalized plan...",
] as const;

export function WorkoutGeneratingStatus() {
    const [messageIndex, setMessageIndex] = useState(0);

    useEffect(() => {
        const interval = setInterval(() => {
            setMessageIndex(prev => (prev + 1) % GENERATING_MESSAGES.length);
        }, 2500);
        return () => clearInterval(interval);
    }, []);

    return (
        <div className="flex flex-col items-center justify-center py-16 gap-6">
            <div className="relative">
                <div className="w-20 h-20 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                    <Sparkles className="w-8 h-8 text-white animate-pulse" />
                </div>
                <div className="absolute inset-0 rounded-full bg-blue-400/30 animate-ping" />
            </div>

            <div className="text-center space-y-2">
                <h3 className="text-xl font-semibold text-slate-900">Generating Your Plan</h3>
                <p className="text-slate-500 text-sm h-5 transition-opacity duration-500">
                    {GENERATING_MESSAGES[messageIndex]}
                </p>
            </div>

            <div className="w-56 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                <div className="h-full w-full bg-gradient-to-r from-blue-500 to-indigo-500 animate-pulse rounded-full" />
            </div>

            <p className="text-xs text-slate-400">This usually takes 1–2 minutes</p>
        </div>
    );
}
