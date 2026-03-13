import { z } from "zod";
import { GENDERS, EXPERIENCE_LEVELS } from "@/constants/const";

export const fitnessInfoSchema = z.object({
        height: z.coerce
    .number()
    .positive("Height must be a positive number.")
    .max(300, "Height must not exceed 300 cm.")
    .multipleOf(0.01, "Maximum 2 decimal places allowed."),

    weight: z.coerce
    .number()
    .positive("Weight must be a positive number.")
    .max(500, "Weight must not exceed 500 kg.")
    .multipleOf(0.01, "Maximum 2 decimal places allowed."),

    age: z.coerce
    .number()
    .int("Age must be a whole number.")
    .positive("Age must be a positive number.")
    .max(120, "Age must not exceed 120."),

    gender: z.enum(GENDERS, {
    message: "Please select a valid gender.",
}),

    experience_level: z.enum(EXPERIENCE_LEVELS, {
        message: "Please select a valid experience level.",
    }),
});


export const userProfileShcema = z.object({
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
});