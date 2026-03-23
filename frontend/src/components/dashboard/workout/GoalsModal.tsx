import { Check } from "lucide-react";
import { Button } from "@/components/ui/button";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import { FITNESS_GOALS } from "@/constants/const";

interface GoalsModalProps {
    isOpen: boolean;
    selectedGoal: string;
    onToggle: (goal: string) => void;
    onClose: () => void;
}

export function GoalsModal({ isOpen, selectedGoal, onToggle, onClose }: GoalsModalProps) {
    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent showCloseButton className="sm:max-w-2xl max-h-[90vh] flex flex-col p-0">
                <DialogHeader className="px-6 pt-6 pb-4 border-b border-slate-200 flex-shrink-0">
                    <DialogTitle>All Fitness Goals</DialogTitle>
                    <DialogDescription>Select your primary goal</DialogDescription>
                </DialogHeader>

                <div className="overflow-y-auto flex-1 px-6 py-4">
                    <div className="grid sm:grid-cols-2 gap-3">
                        {FITNESS_GOALS.map((goal) => {
                            const isSelected = selectedGoal === goal.value;
                            return (
                                <button
                                    key={goal.value}
                                    onClick={() => onToggle(goal.value)}
                                    className={`p-4 border-2 rounded-xl transition-all text-left ${
                                        isSelected
                                            ? "border-blue-600 bg-blue-50"
                                            : "border-slate-200 hover:border-blue-300 hover:bg-blue-50/50"
                                    }`}
                                >
                                    <div className="flex items-start gap-3">
                                        <span className="text-2xl">{goal.icon}</span>
                                        <div className="flex-1 min-w-0">
                                            <p className={`font-semibold text-sm ${isSelected ? "text-blue-600" : "text-slate-900"}`}>
                                                {goal.label}
                                            </p>
                                            <p className="text-xs text-slate-500 mt-0.5">{goal.description}</p>
                                        </div>
                                        {isSelected && <Check className="w-4 h-4 text-blue-600 flex-shrink-0 mt-0.5" />}
                                    </div>
                                </button>
                            );
                        })}
                    </div>
                </div>

                <DialogFooter className="px-6 py-4 border-t border-slate-200 flex-shrink-0">
                    <Button
                        onClick={onClose}
                        className="w-full bg-gradient-to-r from-blue-600 to-indigo-600"
                        disabled={!selectedGoal}
                    >
                        Confirm Selection <Check className="w-4 h-4 ml-2" />
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
