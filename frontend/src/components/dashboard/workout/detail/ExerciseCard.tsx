import { Info, Repeat, TrendingUp, Weight, Timer, Clock } from "lucide-react";
import type { BlockExercise } from "@/types/workout";

type Props = {
    exercise: BlockExercise;
    dayId: number;
    blockId: number;
    blockName: string;
    accentClass: string;
    onUpdate: (
        dayId: number,
        blockId: number,
        exerciseId: number,
        field: "sets" | "reps" | "weight" | "duration_seconds" | "rest_seconds" | "rpe",
        value: string,
    ) => void;
};

const inputClass =
    "w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm";

export const ExerciseCard = ({ exercise, dayId, blockId, accentClass, onUpdate }: Props) => {
    const ex = exercise.exercise;

    return (
        <div className={`bg-white rounded-lg border border-slate-200 border-l-4 ${accentClass} p-4`}>
            {/* Name & metadata */}
            <div className="mb-3">
                <h5 className="font-semibold text-slate-900">{ex.name}</h5>
                <div className="flex flex-wrap items-center gap-2 text-xs text-slate-500 mt-1">
                    <span className="capitalize">{ex.category}</span>
                    {ex.muscle_group && (
                        <>
                            <span>•</span>
                            <span className="capitalize">{ex.muscle_group}</span>
                        </>
                    )}
                    {ex.equipment && (
                        <>
                            <span>•</span>
                            <span className="capitalize">{ex.equipment}</span>
                        </>
                    )}
                </div>
            </div>

            {/* Editable parameters */}
            <div className="grid grid-cols-2 md:grid-cols-6 gap-3">
                {exercise.sets !== null && (
                    <div>
                        <label className="text-xs text-slate-600 mb-1 flex items-center gap-1">
                            <Repeat className="w-3 h-3" />
                            Sets
                        </label>
                        <input
                            type="number"
                            min="0"
                            value={exercise.sets ?? ""}
                            onChange={(e) =>
                                onUpdate(dayId, blockId, exercise.id, "sets", e.target.value)
                            }
                            className={inputClass}
                        />
                    </div>
                )}

                {exercise.reps !== null && (
                    <div>
                        <label className="text-xs text-slate-600 mb-1 flex items-center gap-1">
                            <TrendingUp className="w-3 h-3" />
                            Reps
                        </label>
                        <input
                            type="number"
                            min="0"
                            value={exercise.reps ?? ""}
                            onChange={(e) =>
                                onUpdate(dayId, blockId, exercise.id, "reps", e.target.value)
                            }
                            className={inputClass}
                        />
                    </div>
                )}

                {exercise.weight !== null && (
                    <div>
                        <label className="text-xs text-slate-600 mb-1 flex items-center gap-1">
                            <Weight className="w-3 h-3" />
                            Weight (kg)
                        </label>
                        <input
                            type="number"
                            min="0"
                            step="0.5"
                            value={exercise.weight ?? ""}
                            onChange={(e) =>
                                onUpdate(dayId, blockId, exercise.id, "weight", e.target.value)
                            }
                            className={inputClass}
                        />
                    </div>
                )}

                {exercise.duration_seconds !== null && (
                    <div>
                        <label className="text-xs text-slate-600 mb-1 flex items-center gap-1">
                            <Timer className="w-3 h-3" />
                            Duration (s)
                        </label>
                        <input
                            type="number"
                            min="0"
                            value={exercise.duration_seconds ?? ""}
                            onChange={(e) =>
                                onUpdate(
                                    dayId,
                                    blockId,
                                    exercise.id,
                                    "duration_seconds",
                                    e.target.value,
                                )
                            }
                            className={inputClass}
                        />
                    </div>
                )}

                {exercise.rest_seconds !== null && (
                    <div>
                        <label className="text-xs text-slate-600 mb-1 flex items-center gap-1">
                            <Clock className="w-3 h-3" />
                            Rest (s)
                        </label>
                        <input
                            type="number"
                            min="0"
                            value={exercise.rest_seconds ?? ""}
                            onChange={(e) =>
                                onUpdate(
                                    dayId,
                                    blockId,
                                    exercise.id,
                                    "rest_seconds",
                                    e.target.value,
                                )
                            }
                            className={inputClass}
                        />
                    </div>
                )}

                {exercise.rpe !== null && (
                    <div>
                        <label className="text-xs text-slate-600 mb-1 flex items-center gap-1">
                            <TrendingUp className="w-3 h-3" />
                            RPE
                        </label>
                        <input
                            type="number"
                            min="0"
                            max="10"
                            step="0.5"
                            value={exercise.rpe ?? ""}
                            onChange={(e) =>
                                onUpdate(dayId, blockId, exercise.id, "rpe", e.target.value)
                            }
                            className={inputClass}
                        />
                    </div>
                )}
            </div>

            {/* Instructions */}
            {ex.instructions && (
                <div className="mt-3 p-3 bg-blue-50 border border-blue-100 rounded-lg">
                    <div className="flex items-start gap-2">
                        <Info className="w-4 h-4 text-blue-600 flex-shrink-0 mt-0.5" />
                        <div>
                            <p className="text-xs font-medium text-blue-900 mb-1">Instructions</p>
                            <p className="text-xs text-blue-700">{ex.instructions}</p>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};
