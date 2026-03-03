import { z } from "zod";
import { registerSchema, loginSchema, forgotPasswordSchema, resetPasswordSchema } from "../components/forms/authentication";

export type RegisterFormData = z.infer<typeof registerSchema>;
export type RegisterFormErrors = Partial<Record<keyof RegisterFormData, string>>;

export type LoginFormData = z.infer<typeof loginSchema>;
export type LoginFormErrors = Partial<Record<keyof LoginFormData, string>>;

export type ForgotPasswordFormData = z.infer<typeof forgotPasswordSchema>;
export type ForgotPasswordFormErrors = Partial<Record<keyof ForgotPasswordFormData, string>>;

export type ResetPasswordFormData = z.infer<typeof resetPasswordSchema>;
export type ResetPasswordFormErrors = Partial<Record<keyof ResetPasswordFormData, string>>;