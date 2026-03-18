import { z } from "zod";
import { registerSchema, loginSchema, forgotPasswordSchema, resetPasswordSchema, changePasswordSchema } from "../components/forms/authentication";
import { fitnessInfoSchema, userProfileShcema } from "../components/forms/user";

export type RegisterFormData = z.infer<typeof registerSchema>;
export type RegisterFormErrors = Partial<Record<keyof RegisterFormData, string>>;

export type LoginFormData = z.infer<typeof loginSchema>;
export type LoginFormErrors = Partial<Record<keyof LoginFormData, string>>;

export type ForgotPasswordFormData = z.infer<typeof forgotPasswordSchema>;
export type ForgotPasswordFormErrors = Partial<Record<keyof ForgotPasswordFormData, string>>;

export type ResetPasswordFormData = z.infer<typeof resetPasswordSchema>;
export type ResetPasswordFormErrors = Partial<Record<keyof ResetPasswordFormData, string>>;

export type ChangePasswordFormData = z.infer<typeof changePasswordSchema>;
export type ChangePasswordFormErrors = Partial<Record<keyof ChangePasswordFormData, string>>;

export type FitnessInfoFormData = z.infer<typeof fitnessInfoSchema>;
export type FitnessInfoFormErrors = Partial<Record<keyof FitnessInfoFormData, string>>;

export type UserProfileFormData = z.infer<typeof userProfileShcema>;
export type UserProfileFormErrors = Partial<Record<keyof UserProfileFormData, string>>;


