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
import { EQUIPMENT_OPTIONS } from "@/constants/const";

const BODYWEIGHT_ONLY = "Bodyweight Only" as const;

const EQUIPMENT_ICONS: Record<string, string> = {
    "Dumbbells": "💪",
    "Barbells": "🏋️",
    "Resistance Bands": "🔗",
    "Pull-up Bar": "🤸",
    "Bench": "🪑",
    "Kettlebells": "⚫",
    "Cable Machine": "⚙️",
    "Cardio Equipment": "🚴",
    "Bodyweight Only": "🏃",
};

interface EquipmentModalProps {
    isOpen: boolean;
    selectedEquipment: string[];
    isLocked: boolean;
    onToggle: (equipment: string) => void;
    onClose: () => void;
}

export function EquipmentModal({ isOpen, selectedEquipment, isLocked, onToggle, onClose }: EquipmentModalProps) {
    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent showCloseButton className="sm:max-w-2xl max-h-[90vh] flex flex-col p-0">
                <DialogHeader className="px-6 pt-6 pb-4 border-b border-slate-200 flex-shrink-0">
                    <DialogTitle>All Equipment</DialogTitle>
                    <DialogDescription>Select all the equipment you have access to</DialogDescription>
                </DialogHeader>

                <div className="overflow-y-auto flex-1 px-6 py-4">
                    <div className="grid sm:grid-cols-2 gap-3">
                        {EQUIPMENT_OPTIONS.map((equipment) => {
                            const isBodyweight = equipment === BODYWEIGHT_ONLY;
                            const isSelected = isLocked
                                ? !isBodyweight
                                : selectedEquipment.includes(equipment);
                            const isDisabled = isLocked;

                            return (
                                <button
                                    key={equipment}
                                    onClick={() => !isDisabled && onToggle(equipment)}
                                    disabled={isDisabled}
                                    className={`p-4 border-2 rounded-xl transition-all text-left ${
                                        isSelected
                                            ? isDisabled
                                                ? "border-blue-400 bg-blue-50 cursor-not-allowed opacity-70"
                                                : "border-blue-600 bg-blue-50"
                                            : isDisabled
                                                ? "border-slate-200 cursor-not-allowed opacity-50"
                                                : "border-slate-200 hover:border-blue-300 hover:bg-blue-50/50"
                                    }`}
                                >
                                    <div className="flex items-center gap-3">
                                        <span className="text-2xl">{EQUIPMENT_ICONS[equipment] ?? "🏋️"}</span>
                                        <div className="flex-1 min-w-0">
                                            <p className={`font-semibold text-sm ${isSelected && !isDisabled ? "text-blue-600" : "text-slate-900"}`}>
                                                {equipment}
                                            </p>
                                        </div>
                                        {isSelected && (
                                            <Check className={`w-4 h-4 flex-shrink-0 ${isDisabled ? "text-blue-400" : "text-blue-600"}`} />
                                        )}
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
                    >
                        Done <Check className="w-4 h-4 ml-2" />
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
