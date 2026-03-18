import { useState } from "react";
import { useAuth } from "@/contexts/AuthContext";
import { updatePersonalInfo } from "@/services/user";
import { userProfileShcema } from "@/components/forms/user";
import type { UserProfileFormErrors } from "@/types/forms";
import { ApiError } from "@/lib/apiError";
import { toast } from "sonner";

export type ProfileFormState = {
    name: string;
    surname: string;
    email: string;
};

export function useProfileForm() {
    const { user, updateUser } = useAuth();

    const [form, setForm] = useState<ProfileFormState>({
        name: user?.name ?? "",
        surname: user?.surname ?? "",
        email: user?.email ?? "",
    });
    const [errors, setErrors] = useState<UserProfileFormErrors>({});
    const [isLoading, setIsLoading] = useState(false);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();

        const result = userProfileShcema.safeParse(form);
        if (!result.success) {
            const fieldErrors = result.error.flatten().fieldErrors;
            setErrors(
                Object.fromEntries(
                    Object.entries(fieldErrors).map(([k, v]) => [k, v?.[0]])
                ) as UserProfileFormErrors
            );
            return;
        }

        setErrors({});
        setIsLoading(true);

        try {
            const updated = await updatePersonalInfo(user!.id, result.data);
            updateUser(updated);
            toast.success("Profile updated.", {
                position: "top-center",
                duration: 5000,
                style: { background: "#22C55E", color: "#fff" },
            });
        } catch (error: unknown) {
            if (error instanceof ApiError) {
                if (error.fieldErrors) {
                    setErrors(
                        Object.fromEntries(
                            Object.entries(error.fieldErrors).map(([k, v]) => [k, (v as string[])[0]])
                        ) as UserProfileFormErrors
                    );
                } else {
                    toast.error(error.message, {
                        position: "top-center",
                        duration: 5000,
                        style: { background: "#FF4D4F", color: "#fff" },
                    });
                }
            }
        } finally {
            setIsLoading(false);
        }
    };

    return { form, setForm, errors, isLoading, handleSubmit };
}
