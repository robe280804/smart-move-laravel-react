import { z } from "zod";

// Register
export const registerSchema = z
    .object({
        name: z
            .string()
            .min(2, "Name must be at least 2 characters.")
            .max(20, "Name must not exceed 20 characters.")
            .regex(/^[A-Za-z]+$/, "Name can only contain letters."),

        surname: z
            .string()
            .min(2, "Surname must be at least 2 characters.")
            .max(20, "Surname must not exceed 20 characters.")
            .regex(/^[A-Za-z]+$/, "Surname can only contain letters."),

        email: z
            .string()
            .email("Please enter a valid email address.")
            .max(100, "Email must not exceed 100 characters."),

        password: z
            .string()
            .min(8, "Password must be at least 8 characters.")
            .max(64, "Password must not exceed 64 characters.")
            .regex(
                /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])/,
                "Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character."
            ),

        password_confirmation: z.string(),
    })
    .refine((data) => data.password === data.password_confirmation, {
        message: "Passwords do not match.",
        path: ["password_confirmation"],
    });


// Forgot password
export const forgotPasswordSchema = z.object({
    email: z
        .string()
        .email("Please enter a valid email address.")
        .max(100, "Email must not exceed 100 characters."),
});


// Reset password
export const resetPasswordSchema = z
    .object({
        password: z
            .string()
            .min(8, "Password must be at least 8 characters.")
            .max(64, "Password must not exceed 64 characters.")
            .regex(
                /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])/,
                "Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character."
            ),

        password_confirmation: z.string(),
    })
    .refine((data) => data.password === data.password_confirmation, {
        message: "Passwords do not match.",
        path: ["password_confirmation"],
    });


// Login
export const loginSchema = z
    .object({
        email: z
            .string()
            .email("Please enter a valid email address.")
            .max(100, "Email must not exceed 100 characters."),

        password: z
            .string()
            .min(8, "Password must be at least 8 characters.")
            .max(64, "Password must not exceed 64 characters.")
            .regex(
                /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])/,
                "Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character."
            ),
    });
