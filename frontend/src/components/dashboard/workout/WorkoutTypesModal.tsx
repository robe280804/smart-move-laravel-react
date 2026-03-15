import { Check, X } from "lucide-react";
import { Button } from "@/components/ui/button";
import { WORKOUT_TYPES } from "@/constants/const";

interface WorkoutTypesModalProps {
    isOpen: boolean;
    selectedTypes: string[];
    onToggle: (type: string) => void;
    onClose: () => void;
}

export function WorkoutTypesModal({ isOpen, selectedTypes, onToggle, onClose }: WorkoutTypesModalProps) {
    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div className="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[80vh] flex flex-col mx-4">
                <div className="flex items-center justify-between p-6 border-b border-slate-200">
                    <div>
                        <h2 className="text-xl font-bold text-slate-900">All Workout Types</h2>
                        <p className="text-sm text-slate-500 mt-0.5">Select up to 3 types</p>
                    </div>
                    <div className="flex items-center gap-3">
                        {selectedTypes.length > 0 && (
                            <span className="text-sm text-blue-600 font-medium">{selectedTypes.length}/3 selected</span>
                        )}
                        <button
                            onClick={onClose}
                            className="p-2 rounded-lg hover:bg-slate-100 transition-colors"
                        >
                            <X className="w-5 h-5 text-slate-600" />
                        </button>
                    </div>
                </div>
                <div className="overflow-y-auto flex-1 p-6">
                    <div className="grid md:grid-cols-2 gap-3">
                        {WORKOUT_TYPES.map((type) => {
                            const isSelected = selectedTypes.includes(type.value);
                            return (
                                <button
                                    key={type.value}
                                    onClick={() => onToggle(type.value)}
                                    className={`p-4 border-2 rounded-xl transition-all text-left ${isSelected
                                            ? "border-blue-600 bg-blue-50"
                                            : selectedTypes.length >= 3
                                                ? "border-slate-200 opacity-50 cursor-not-allowed"
                                                : "border-slate-200 hover:border-blue-600 hover:bg-blue-50"
                                        }`}
                                >
                                    <div className="flex items-center gap-3">
                                        <span className="text-2xl">{type.icon}</span>
                                        <span className={`font-medium flex-1 ${isSelected ? "text-blue-600" : "text-slate-900"}`}>
                                            {type.label}
                                        </span>
                                        {isSelected && <Check className="w-5 h-5 text-blue-600 flex-shrink-0" />}
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
                        disabled={selectedTypes.length === 0}
                    >
                        Confirm {selectedTypes.length > 0 ? `(${selectedTypes.length} selected)` : ""} <Check className="w-4 h-4 ml-2" />
                    </Button>
                </div>
            </div>
        </div>
    );
}
