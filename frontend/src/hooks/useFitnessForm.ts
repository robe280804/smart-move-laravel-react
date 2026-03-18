import { useState, useEffect } from "react";
import { getFitnessInfo, storeFitnessInfo, updateFitnessInfo } from "@/services/user";
import type { FitnessInfo } from "@/types/user";
import type { ExperienceLevel, Gender } from "@/constants/const";
import { fitnessInfoSchema } from "@/components/forms/user";
import type { FitnessInfoFormErrors } from "@/types/forms";
import { ApiError } from "@/lib/apiError";
import { notify } from "@/lib/toast";

export type FitnessFormState = {
    height: string;
    weight: string;
    age: string;
    gender: Gender | "";
    experience_level: ExperienceLevel | "";
};

export function useFitnessForm() {
    const [fitnessInfo, setFitnessInfo] = useState<FitnessInfo | null>(null);
    const [form, setForm] = useState<FitnessFormState>({
        height: "",
        weight: "",
        age: "",
        gender: "",
        experience_level: "",
    });
    const [errors, setErrors] = useState<FitnessInfoFormErrors>({});
    const [isLoading, setIsLoading] = useState(false);

    useEffect(() => {
        getFitnessInfo()
            .then((data) => {
                if (!data) {
                    setFitnessInfo(null);
                    return;
                }
                setFitnessInfo(data);
                setForm({
                    height: data.height ? String(data.height) : "",
                    weight: data.weight ? String(data.weight) : "",
                    age: data.age ? String(data.age) : "",
                    gender: data.gender ?? "",
                    experience_level: data.experience_level ?? "",
                });
            })
            .catch(() => setFitnessInfo(null));
    }, []);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();

        const result = fitnessInfoSchema.safeParse(form);
        if (!result.success) {
            const fieldErrors = result.error.flatten().fieldErrors;
            setErrors(
                Object.fromEntries(
                    Object.entries(fieldErrors).map(([k, v]) => [k, v?.[0]])
                ) as FitnessInfoFormErrors
            );
            return;
        }

        setErrors({});
        setIsLoading(true);

        try {
            if (!fitnessInfo?.id) {
                const created = await storeFitnessInfo(result.data);
                setFitnessInfo(created);
                notify.success("Fitness profile created.");
            } else {
                const updated = await updateFitnessInfo(fitnessInfo.id, result.data);
                setFitnessInfo(updated);
                notify.success("Fitness profile updated.");
            }
        } catch (error: unknown) {
            if (error instanceof ApiError) {
                if (error.fieldErrors) {
                    setErrors(
                        Object.fromEntries(
                            Object.entries(error.fieldErrors).map(([k, v]) => [k, (v as string[])[0]])
                        ) as FitnessInfoFormErrors
                    );
                } else {
                    notify.error(error.message);
                }
            }
        } finally {
            setIsLoading(false);
        }
    };

    return { fitnessInfo, form, setForm, errors, isLoading, handleSubmit };
}
