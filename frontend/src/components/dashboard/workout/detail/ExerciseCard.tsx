import { Info, Repeat, TrendingUp, Weight, Timer, Clock, Lock } from "lucide-react";
import type { BlockExercise } from "@/types/workout";
import type { ExerciseField, ExerciseFieldErrors } from "@/hooks/useWorkoutPlan";

type Props = {
    exercise: BlockExercise;
    dayId: number;
    blockId: number;
    blockName: string;
    accentClass: string;
    canEdit: boolean;
    errors: ExerciseFieldErrors;
    onUpdate: (dayId: number, blockId: number, exerciseId: number, field: ExerciseField, value: string) => void;
};

const inputClass =
    "w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm";
const inputErrorClass =
    "w-full px-3 py-2 border border-red-400 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 text-sm bg-red-50";
const inputReadonlyClass =
    "w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 text-slate-500 cursor-not-allowed";

type FieldInputProps = {
    field: ExerciseField;
    label: string;
    icon: React.ReactNode;
    value: string | number | null;
    min: number;
    max: number;
    step?: string;
    canEdit: boolean;
    error?: string;
    onUpdate: (field: ExerciseField, value: string) => void;
};

const FieldInput = ({ field, label, icon, value, min, max, step, canEdit, error, onUpdate }: FieldInputProps) => (
    <div>
        <label className="text-xs text-slate-600 mb-1 flex items-center gap-1">
            {icon}
            {label}
        </label>
        <input
            type="number"
            min={min}
            max={max}
            step={step}
            value={value ?? ""}
            readOnly={!canEdit}
            onChange={(e) => canEdit && onUpdate(field, e.target.value)}
            className={!canEdit ? inputReadonlyClass : error ? inputErrorClass : inputClass}
        />
        {error && <p className="mt-1 text-xs text-red-500">{error}</p>}
    </div>
);

export const ExerciseCard = ({ exercise, dayId, blockId, accentClass, canEdit, errors, onUpdate }: Props) => {
    const ex = exercise.exercise;

    const handleUpdate = (field: ExerciseField, value: string) => {
        onUpdate(dayId, blockId, exercise.id, field, value);
    };

    return (
        <div className={`bg-white border-l-4 ${accentClass} p-4`}>
            {/* Name & metadata */}
            <div className="mb-3">
                <h5 className="font-semibold text-slate-900" translate="no">{ex.name}</h5>
                <div className="flex flex-wrap items-center gap-2 text-xs text-slate-500 mt-1">
                    <span className="capitalize" translate="no">{ex.category}</span>
                    {ex.muscle_group && (
                        <>
                            <span>•</span>
                            <span className="capitalize" translate="no">{ex.muscle_group}</span>
                        </>
                    )}
                    {ex.equipment && (
                        <>
                            <span>•</span>
                            <span className="capitalize" translate="no">{ex.equipment}</span>
                        </>
                    )}
                </div>
            </div>

            {/* Parameters */}
            <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
                {exercise.sets !== null && (
                    <FieldInput
                        field="sets"
                        label="Sets"
                        icon={<Repeat className="w-3 h-3" />}
                        value={exercise.sets}
                        min={0}
                        max={100}
                        canEdit={canEdit}
                        error={errors.sets}
                        onUpdate={handleUpdate}
                    />
                )}

                {exercise.reps !== null && (
                    <FieldInput
                        field="reps"
                        label="Reps"
                        icon={<TrendingUp className="w-3 h-3" />}
                        value={exercise.reps}
                        min={0}
                        max={100}
                        canEdit={canEdit}
                        error={errors.reps}
                        onUpdate={handleUpdate}
                    />
                )}

                {exercise.weight !== null && (
                    <FieldInput
                        field="weight"
                        label="Weight (kg)"
                        icon={<Weight className="w-3 h-3" />}
                        value={exercise.weight}
                        min={0}
                        max={800}
                        step="0.5"
                        canEdit={canEdit}
                        error={errors.weight}
                        onUpdate={handleUpdate}
                    />
                )}

                {exercise.duration_seconds !== null && (
                    <FieldInput
                        field="duration_seconds"
                        label="Duration (s)"
                        icon={<Timer className="w-3 h-3" />}
                        value={exercise.duration_seconds}
                        min={0}
                        max={3600}
                        canEdit={canEdit}
                        error={errors.duration_seconds}
                        onUpdate={handleUpdate}
                    />
                )}

                {exercise.rest_seconds !== null && (
                    <FieldInput
                        field="rest_seconds"
                        label="Rest (s)"
                        icon={<Clock className="w-3 h-3" />}
                        value={exercise.rest_seconds}
                        min={0}
                        max={600}
                        canEdit={canEdit}
                        error={errors.rest_seconds}
                        onUpdate={handleUpdate}
                    />
                )}

                {exercise.rpe !== null && (
                    <FieldInput
                        field="rpe"
                        label="RPE"
                        icon={<TrendingUp className="w-3 h-3" />}
                        value={exercise.rpe}
                        min={0}
                        max={10}
                        step="0.5"
                        canEdit={canEdit}
                        error={errors.rpe}
                        onUpdate={handleUpdate}
                    />
                )}
            </div>

            {!canEdit && (
                <div className="mt-3 flex items-center gap-2 text-xs text-slate-500">
                    <Lock className="w-3.5 h-3.5 shrink-0" />
                    <span>Upgrade to Advanced or Pro to edit exercises.</span>
                </div>
            )}

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
