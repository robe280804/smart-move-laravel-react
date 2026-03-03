import { useState } from "react";
import { Link, useNavigate, useSearchParams } from "react-router-dom";
import { Dumbbell, Eye, EyeOff, KeyRound } from "lucide-react";
import { Button } from "../components/ui/button";
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from "../components/ui/card";
import { Input } from "../components/ui/input";
import { Label } from "../components/ui/label";
import { resetPasswordSchema } from "../components/forms/authentication";
import type { ResetPasswordFormData, ResetPasswordFormErrors } from "../types/forms";
import { resetPassword } from "../services/authentication";
import { ApiError } from "../lib/apiError";
import { toast } from "sonner";

export const ResetPassword = () => {
    const navigate = useNavigate();
    const [searchParams] = useSearchParams();

    const token = searchParams.get("token");
    const email = searchParams.get("email");

    const [form, setForm] = useState<ResetPasswordFormData>({
        password: "",
        password_confirmation: "",
    });
    const [errors, setErrors] = useState<ResetPasswordFormErrors>({});
    const [isLoading, setIsLoading] = useState(false);
    const [showPassword, setShowPassword] = useState(false);
    const [showConfirmPassword, setShowConfirmPassword] = useState(false);

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const { name, value } = e.target;
        setForm((prev) => ({ ...prev, [name]: value }));
        if (errors[name as keyof ResetPasswordFormErrors]) {
            setErrors((prev) => ({ ...prev, [name]: undefined }));
        }
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        const result = resetPasswordSchema.safeParse(form);
        if (!result.success) {
            const fieldErrors = result.error.flatten().fieldErrors;
            setErrors(
                Object.fromEntries(
                    Object.entries(fieldErrors).map(([k, v]) => [k, v?.[0]])
                ) as ResetPasswordFormErrors
            );
            return;
        }
        setIsLoading(true);
        setErrors({});
        try {
            await resetPassword(token!, email!, result.data.password, result.data.password_confirmation);
            toast.success("Password reset successfully. You can now sign in.", {
                position: "top-center",
                duration: 5000,
            });
            navigate("/login");
        } catch (error: unknown) {
            if (error instanceof ApiError) {
                toast.error(error.message, {
                    position: "top-center",
                    duration: 5000,
                    style: { background: "#FF4D4F", color: "#fff" },
                });
            }
        } finally {
            setIsLoading(false);
        }
    };

    // Guard: token or email missing from URL
    if (!token || !email) {
        return (
            <div className="relative z-10 w-full max-w-md text-center space-y-4">
                <div className="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto">
                    <KeyRound className="w-6 h-6 text-red-500" />
                </div>
                <h2 className="text-xl font-semibold text-slate-800">Invalid reset link</h2>
                <p className="text-slate-500 text-sm">
                    This password reset link is missing required information.
                    Please request a new one.
                </p>
                <Link
                    to="/forgot-password"
                    className="inline-block text-sm text-blue-600 hover:text-blue-700 font-semibold"
                >
                    Request a new link
                </Link>
            </div>
        );
    }

    return (
        <div className="relative z-10 w-full max-w-md">
            {/* Logo */}
            <div className="flex items-center justify-center gap-2 mb-8">
                <div className="w-12 h-12 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                    <Dumbbell className="w-7 h-7 text-white" />
                </div>
                <span className="text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                    Smart Move AI
                </span>
            </div>

            <Card className="border-0 shadow-2xl">
                <CardHeader className="space-y-1">
                    <CardTitle className="text-2xl text-center">Set new password</CardTitle>
                    <CardDescription className="text-center">
                        Choose a strong password for{" "}
                        <span className="font-medium text-slate-700">{email}</span>
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <form onSubmit={handleSubmit} noValidate className="space-y-4">
                        {/* New password */}
                        <div className="space-y-2">
                            <Label htmlFor="password">New password</Label>
                            <div className="relative">
                                <Input
                                    id="password"
                                    name="password"
                                    type={showPassword ? "text" : "password"}
                                    placeholder="••••••••"
                                    autoComplete="new-password"
                                    value={form.password}
                                    onChange={handleChange}
                                    aria-invalid={!!errors.password}
                                    className={errors.password ? "border-red-500 pr-10" : "pr-10"}
                                />
                                <button
                                    type="button"
                                    onClick={() => setShowPassword((v) => !v)}
                                    className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-700"
                                >
                                    {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                                </button>
                            </div>
                            {errors.password && (
                                <p className="text-sm text-red-500">{errors.password}</p>
                            )}
                        </div>

                        {/* Confirm new password */}
                        <div className="space-y-2">
                            <Label htmlFor="password_confirmation">Confirm new password</Label>
                            <div className="relative">
                                <Input
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    type={showConfirmPassword ? "text" : "password"}
                                    placeholder="••••••••"
                                    autoComplete="new-password"
                                    value={form.password_confirmation}
                                    onChange={handleChange}
                                    aria-invalid={!!errors.password_confirmation}
                                    className={errors.password_confirmation ? "border-red-500 pr-10" : "pr-10"}
                                />
                                <button
                                    type="button"
                                    onClick={() => setShowConfirmPassword((v) => !v)}
                                    className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-700"
                                >
                                    {showConfirmPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                                </button>
                            </div>
                            {errors.password_confirmation && (
                                <p className="text-sm text-red-500">{errors.password_confirmation}</p>
                            )}
                        </div>

                        <Button
                            type="submit"
                            className="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700"
                            disabled={isLoading}
                        >
                            <KeyRound className="w-4 h-4 mr-2" />
                            {isLoading ? "Resetting…" : "Reset password"}
                        </Button>
                    </form>
                </CardContent>

                <CardFooter>
                    <p className="text-sm text-center w-full text-slate-600">
                        Remembered your password?{" "}
                        <Link to="/login" className="text-blue-600 hover:text-blue-700 font-semibold">
                            Sign in
                        </Link>
                    </p>
                </CardFooter>
            </Card>
        </div>
    );
};
