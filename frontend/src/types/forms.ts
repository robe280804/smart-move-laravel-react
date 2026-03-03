import { z } from "zod";
import { registerSchema, loginSchema } from "../components/forms/authentication";

export type RegisterFormData = z.infer<typeof registerSchema>;
export type RegisterFormErrors = Partial<Record<keyof RegisterFormData, string>>;


export type LoginFormData = z.infer<typeof loginSchema>;
export type LoginFormErrors = Partial<Record<keyof LoginFormData, string>>;