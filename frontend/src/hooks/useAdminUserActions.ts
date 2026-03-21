import { useState } from "react";
import { adminUpdateUserSchema, type AdminUpdateUserFormData, type AdminUpdateUserFormErrors } from "@/components/forms/adminUser";
import { updateAdminUser, deleteAdminUser } from "@/services/admin";
import { ApiError } from "@/lib/apiError";
import { notify } from "@/lib/toast";
import type { User } from "@/types/auth";

export function useAdminUserActions(onSuccess: () => void) {
    const [editingUser, setEditingUser] = useState<User | null>(null);
    const [deletingUser, setDeletingUser] = useState<User | null>(null);
    const [form, setForm] = useState<AdminUpdateUserFormData>({ name: "", surname: "", email: "", role: "user" });
    const [errors, setErrors] = useState<AdminUpdateUserFormErrors>({});
    const [isLoading, setIsLoading] = useState(false);

    const openEdit = (user: User) => {
        setEditingUser(user);
        setForm({
            name: user.name,
            surname: user.surname,
            email: user.email,
            role: user.role ?? "user",
        });
        setErrors({});
    };

    const closeEdit = () => {
        setEditingUser(null);
        setErrors({});
    };

    const openDelete = (user: User) => {
        setDeletingUser(user);
    };

    const closeDelete = () => {
        setDeletingUser(null);
    };

    const handleUpdate = async (e: React.FormEvent) => {
        e.preventDefault();

        const result = adminUpdateUserSchema.safeParse(form);
        if (!result.success) {
            const fieldErrors = result.error.flatten().fieldErrors;
            setErrors(
                Object.fromEntries(
                    Object.entries(fieldErrors).map(([k, v]) => [k, v?.[0]])
                ) as AdminUpdateUserFormErrors
            );
            return;
        }

        setErrors({});
        setIsLoading(true);

        try {
            await updateAdminUser(editingUser!.id, result.data);
            notify.success("User updated successfully.");
            closeEdit();
            onSuccess();
        } catch (error: unknown) {
            if (error instanceof ApiError) {
                if (error.fieldErrors) {
                    setErrors(
                        Object.fromEntries(
                            Object.entries(error.fieldErrors).map(([k, v]) => [k, (v as string[])[0]])
                        ) as AdminUpdateUserFormErrors
                    );
                } else {
                    notify.error(error.message);
                }
            }
        } finally {
            setIsLoading(false);
        }
    };

    const handleDelete = async () => {
        setIsLoading(true);

        try {
            await deleteAdminUser(deletingUser!.id);
            notify.success("User deleted successfully.");
            closeDelete();
            onSuccess();
        } catch (error: unknown) {
            if (error instanceof ApiError) {
                notify.error(error.message);
            }
        } finally {
            setIsLoading(false);
        }
    };

    return {
        editingUser,
        deletingUser,
        form,
        setForm,
        errors,
        isLoading,
        openEdit,
        closeEdit,
        openDelete,
        closeDelete,
        handleUpdate,
        handleDelete,
    };
}
