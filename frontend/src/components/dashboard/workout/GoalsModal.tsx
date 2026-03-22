import { Check, X } from "lucide-react";
import { Button } from "@/components/ui/button";
import { FITNESS_GOALS } from "@/constants/const";

interface GoalsModalProps {
    isOpen: boolean;
    selectedGoal: string;
    onToggle: (goal: string) => void;
    onClose: () => void;
}

export function GoalsModal({ isOpen, selectedGoal, onToggle, onClose }: GoalsModalProps) {
    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div className="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[80vh] flex flex-col mx-4">
                <div className="flex items-center justify-between p-6 border-b border-slate-200">
                    <div>
                        <h2 className="text-xl font-bold text-slate-900">All Fitness Goals</h2>
                        <p className="text-sm text-slate-500 mt-0.5">Select your primary goal</p>
                    </div>
                    <button
                        onClick={onClose}
                        className="p-2 rounded-lg hover:bg-slate-100 transition-colors"
                    >
                        <X className="w-5 h-5 text-slate-600" />
                    </button>
                </div>
                <div className="overflow-y-auto flex-1 p-6">
                    <div className="grid md:grid-cols-2 gap-3">
                        {FITNESS_GOALS.map((goal) => {
                            const isSelected = selectedGoal === goal.value;
                            return (
                                <button
                                    key={goal.value}
                                    onClick={() => onToggle(goal.value)}
                                    className={`p-4 border-2 rounded-xl transition-all text-left ${
                                        isSelected
                                            ? "border-blue-600 bg-blue-50"
                                            : "border-slate-200 hover:border-blue-600 hover:bg-blue-50"
                                    }`}
                                >
                                    <div className="flex items-start gap-3">
                                        <span className="text-2xl">{goal.icon}</span>
                                        <div className="flex-1">
                                            <p className={`font-semibold ${isSelected ? "text-blue-600" : "text-slate-900"}`}>
                                                {goal.label}
                                            </p>
                                            <p className="text-sm text-slate-600">{goal.description}</p>
                                        </div>
                                        {isSelected && <Check className="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />}
                                    </div>
                                </button>
                            );
                        })}
                    </div>
                </div>
                <div className="p-6 border-t border-slate-200">
                    <Button
                        onClick={onClose}
                        className="w-full bg-gradient-to-r from-blue-600 to-indigo-600"
                        disabled={!selectedGoal}
                    >
                        Confirm <Check className="w-4 h-4 ml-2" />
                    </Button>
                </div>
            </div>
        </div>
    );
}
