import { useState } from "react";
import { changePassword } from "@/services/user";
import { changePasswordSchema } from "@/components/forms/authentication";
import type { ChangePasswordFormErrors } from "@/types/forms";
import { ApiError } from "@/lib/apiError";
import { notify } from "@/lib/toast";

export type SecurityFormState = {
    current_password: string;
    password: string;
    password_confirmation: string;
};

const initialForm: SecurityFormState = {
    current_password: "",
    password: "",
    password_confirmation: "",
};

export function useSecurityForm() {
    const [form, setForm] = useState<SecurityFormState>(initialForm);
    const [errors, setErrors] = useState<ChangePasswordFormErrors>({});
    const [isLoading, setIsLoading] = useState(false);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();

        const result = changePasswordSchema.safeParse(form);
        if (!result.success) {
            const fieldErrors = result.error.flatten().fieldErrors;
            setErrors(
                Object.fromEntries(
                    Object.entries(fieldErrors).map(([k, v]) => [k, v?.[0]])
                ) as ChangePasswordFormErrors
            );
            return;
        }

        setErrors({});
        setIsLoading(true);

        try {
            await changePassword(result.data);
            setForm(initialForm);
            notify.success("Password changed successfully.");
        } catch (error: unknown) {
            if (error instanceof ApiError) {
                if (error.fieldErrors) {
                    setErrors(
                        Object.fromEntries(
                            Object.entries(error.fieldErrors).map(([k, v]) => [k, (v as string[])[0]])
                        ) as ChangePasswordFormErrors
                    );
                } else {
                    notify.error(error.message);
                }
            }
        } finally {
            setIsLoading(false);
        }
    };

    return { form, setForm, errors, isLoading, handleSubmit };
}
