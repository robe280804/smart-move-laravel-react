import { z } from "zod";

export const feedbackSchema = z.object({
    rating: z.number().int().min(1).max(5).nullable(),
    message: z
        .string()
        .max(1000, "Message must not exceed 1000 characters.")
        .nullable(),
});

export type FeedbackFormData = z.infer<typeof feedbackSchema>;
export type FeedbackFormErrors = Partial<Record<keyof FeedbackFormData, string>>;
