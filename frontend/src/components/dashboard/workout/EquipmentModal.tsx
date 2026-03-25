import { Bike, Cable, Check, Dumbbell, GripHorizontal, PersonStanding, Target, Waves, Weight } from "lucide-react";
import type { LucideIcon } from "lucide-react";
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

const EQUIPMENT_CONFIG: Record<string, { icon: LucideIcon; color: string; bg: string }> = {
    "Dumbbells": { icon: Dumbbell, color: "text-violet-600", bg: "bg-violet-100" },
    "Barbells": { icon: Weight, color: "text-blue-600", bg: "bg-blue-100" },
    "Resistance Bands": { icon: Waves, color: "text-emerald-600", bg: "bg-emerald-100" },
    "Pull-up Bar": { icon: GripHorizontal, color: "text-orange-600", bg: "bg-orange-100" },
    "Bench": { icon: Dumbbell, color: "text-amber-600", bg: "bg-amber-100" },
    "Kettlebells": { icon: Target, color: "text-rose-600", bg: "bg-rose-100" },
    "Cable Machine": { icon: Cable, color: "text-slate-600", bg: "bg-slate-200" },
    "Cardio Equipment": { icon: Bike, color: "text-cyan-600", bg: "bg-cyan-100" },
    "Bodyweight Only": { icon: PersonStanding, color: "text-indigo-600", bg: "bg-indigo-100" },
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
                            const config = EQUIPMENT_CONFIG[equipment] ?? { icon: Dumbbell, color: "text-slate-600", bg: "bg-slate-100" };
                            const Icon = config.icon;

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
                                        <div className={`w-9 h-9 rounded-lg ${config.bg} flex items-center justify-center flex-shrink-0`}>
                                            <Icon className={`w-4 h-4 ${config.color}`} />
                                        </div>
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
