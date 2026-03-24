import { useState } from "react";
import { Calendar, Clock, TrendingUp, ChevronDown, ChevronRight } from "lucide-react";
import { DAYS_OF_WEEK } from "@/constants/const";
import type { PlanDay } from "@/types/workout";
import type { ExerciseField, ExerciseFieldErrors } from "@/hooks/useWorkoutPlan";
import { ExerciseCard } from "./ExerciseCard";

// day_of_week: 1 = Monday … 7 = Sunday  →  DAYS_OF_WEEK is 0-indexed from Monday
const getDayName = (day: number): string => DAYS_OF_WEEK[day - 1] ?? "Unknown";

const BLOCK_TOP_COLOR: Record<string, string> = {
    Warmup: "bg-green-400",
    Main: "bg-indigo-400",
    "Cool-down": "bg-amber-400",
    "Mobility Flow": "bg-teal-400",
    Strength: "bg-blue-400",
    Accessory: "bg-purple-400",
    Core: "bg-orange-400",
    Circuit: "bg-pink-400",
    Conditioning: "bg-cyan-400",
};

const BLOCK_ICON_COLORS: Record<string, string> = {
    Warmup: "text-green-600",
    Main: "text-indigo-600",
    "Cool-down": "text-amber-600",
    "Mobility Flow": "text-teal-600",
    Strength: "text-blue-600",
    Accessory: "text-purple-600",
    Core: "text-orange-600",
    Circuit: "text-pink-600",
    Conditioning: "text-cyan-600",
};

const BLOCK_CARD_ACCENT: Record<string, string> = {
    Warmup: "border-l-green-400",
    Main: "border-l-indigo-400",
    "Cool-down": "border-l-amber-400",
    "Mobility Flow": "border-l-teal-400",
    Strength: "border-l-blue-400",
    Accessory: "border-l-purple-400",
    Core: "border-l-orange-400",
    Circuit: "border-l-pink-400",
    Conditioning: "border-l-cyan-400",
};

type UpdateFn = (
    dayId: number,
    blockId: number,
    exerciseId: number,
    field: ExerciseField,
    value: string,
) => void;

type Props = {
    day: PlanDay;
    isExpanded: boolean;
    onToggle: () => void;
    onUpdate: UpdateFn;
    canEdit: boolean;
    fieldErrors: Record<number, ExerciseFieldErrors>;
};

export const WorkoutDayAccordion = ({ day, isExpanded, onToggle, onUpdate, canEdit, fieldErrors }: Props) => {
    const [openBlocks, setOpenBlocks] = useState<Set<number>>(() =>
        new Set(day.workout_blocks.map((b) => b.id)),
    );

    const toggleBlock = (blockId: number) => {
        setOpenBlocks((prev) => {
            const next = new Set(prev);
            if (next.has(blockId)) {
                next.delete(blockId);
            } else {
                next.add(blockId);
            }
            return next;
        });
    };

    return (
        <div className="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            {/* Day header */}
            <button
                onClick={onToggle}
                className="w-full p-4 sm:p-6 flex items-center justify-between hover:bg-slate-50 transition-colors text-left"
            >
                <div className="flex items-center gap-3 min-w-0">
                    <div className="w-11 h-11 sm:w-12 sm:h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex flex-col items-center justify-center flex-shrink-0 leading-none">
                        <span className="text-[10px] font-bold text-blue-200 uppercase tracking-wider">
                            {getDayName(day.day_of_week).slice(0, 3)}
                        </span>
                        <span className="text-xl font-extrabold text-white leading-none">
                            {day.day_of_week}
                        </span>
                    </div>
                    <div className="min-w-0">
                        <h3 className="font-semibold text-slate-900 text-base sm:text-lg truncate">
                            {day.workout_name ?? "Session"}
                        </h3>
                        <div className="flex flex-wrap items-center gap-2 sm:gap-3 text-xs sm:text-sm text-slate-600 mt-1">
                            <span className="flex items-center gap-1">
                                <Calendar className="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                                {getDayName(day.day_of_week)}
                            </span>
                            <span className="flex items-center gap-1">
                                <Clock className="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                                {day.duration_minutes} min
                            </span>
                            <span className="flex items-center gap-1">
                                <TrendingUp className="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                                {day.workout_blocks.length} blocks
                            </span>
                        </div>
                    </div>
                </div>

                {isExpanded ? (
                    <ChevronDown className="w-5 h-5 text-slate-400 flex-shrink-0 ml-2" />
                ) : (
                    <ChevronRight className="w-5 h-5 text-slate-400 flex-shrink-0 ml-2" />
                )}
            </button>

            {/* Expanded content */}
            {isExpanded && (
                <div className="border-t border-slate-200 p-3 sm:p-6 bg-slate-50 space-y-3">
                    {day.workout_blocks.map((block) => {
                        const isBlockOpen = openBlocks.has(block.id);
                        const topColor = BLOCK_TOP_COLOR[block.name] ?? "bg-slate-300";
                        const iconColor = BLOCK_ICON_COLORS[block.name] ?? "text-slate-700";

                        return (
                            <div
                                key={block.id}
                                className="rounded-xl overflow-hidden bg-white border border-slate-200 shadow-sm"
                            >
                                {/* Colored top accent bar */}
                                <div className={`h-1 w-full ${topColor}`} />

                                {/* Block header — clickable */}
                                <button
                                    onClick={() => toggleBlock(block.id)}
                                    className="w-full px-4 py-3 flex items-center justify-between hover:bg-slate-50 transition-colors text-left"
                                >
                                    <div className="flex items-center gap-2">
                                        <h4 className={`font-semibold ${iconColor}`}>
                                            {block.name}
                                        </h4>
                                        <span className="text-xs text-slate-400">
                                            {block.block_exercises.length} exercises
                                        </span>
                                    </div>
                                    {isBlockOpen ? (
                                        <ChevronDown className={`w-4 h-4 ${iconColor}`} />
                                    ) : (
                                        <ChevronRight className={`w-4 h-4 ${iconColor}`} />
                                    )}
                                </button>

                                {/* Exercises */}
                                {isBlockOpen && (
                                    <div className="border-t border-slate-100 divide-y divide-slate-100">
                                        {block.block_exercises.map((blockExercise) => (
                                            <ExerciseCard
                                                key={blockExercise.id}
                                                exercise={blockExercise}
                                                dayId={day.id}
                                                blockId={block.id}
                                                blockName={block.name}
                                                accentClass={
                                                    BLOCK_CARD_ACCENT[block.name] ??
                                                    "border-l-slate-300"
                                                }
                                                canEdit={canEdit}
                                                errors={fieldErrors[blockExercise.id] ?? {}}
                                                onUpdate={onUpdate}
                                            />
                                        ))}
                                    </div>
                                )}
                            </div>
                        );
                    })}
                </div>
            )}
        </div>
    );
};
