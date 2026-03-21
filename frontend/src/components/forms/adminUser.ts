import { z } from "zod";

export const adminUpdateUserSchema = z.object({
    name: z.string().min(1, "Name is required.").max(255, "Name must not exceed 255 characters."),
    surname: z.string().min(1, "Surname is required.").max(255, "Surname must not exceed 255 characters."),
    email: z.string().email("Please provide a valid email address.").max(255),
    role: z.enum(["user", "admin"], { message: "Please select a valid role." }),
});

export type AdminUpdateUserFormData = z.infer<typeof adminUpdateUserSchema>;
export type AdminUpdateUserFormErrors = Partial<Record<keyof AdminUpdateUserFormData, string>>;
