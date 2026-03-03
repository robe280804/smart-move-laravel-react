import { useState } from "react";
import { Link } from "react-router-dom";
import { Dumbbell, Mail, Send } from "lucide-react";
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
import { forgotPasswordSchema } from "../components/forms/authentication";
import type { ForgotPasswordFormData, ForgotPasswordFormErrors } from "../types/forms";
import { forgotPassword } from "../services/authentication";
import { ApiError } from "../lib/apiError";

export const ForgotPassword = () => {
    const [form, setForm] = useState<ForgotPasswordFormData>({ email: "" });
    const [errors, setErrors] = useState<ForgotPasswordFormErrors>({});
    const [isLoading, setIsLoading] = useState(false);
    const [submitted, setSubmitted] = useState(false);

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setForm({ email: e.target.value });
        if (errors.email) setErrors({});
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        const result = forgotPasswordSchema.safeParse(form);
        if (!result.success) {
            setErrors({ email: result.error.flatten().fieldErrors.email?.[0] });
            return;
        }
        setIsLoading(true);
        setErrors({});
        try {
            await forgotPassword(result.data.email);
            setSubmitted(true);
        } catch (error: unknown) {
            // Always show the success state even on error to avoid
            // leaking whether an email exists in the system.
            if (error instanceof ApiError && error.statusCode !== 422) {
                setSubmitted(true);
            } else if (error instanceof ApiError && error.fieldErrors) {
                setErrors(
                    Object.fromEntries(
                        Object.entries(error.fieldErrors).map(([k, v]) => [k, v[0]])
                    ) as ForgotPasswordFormErrors
                );
            }
        } finally {
            setIsLoading(false);
        }
    };

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
                {!submitted ? (
                    <>
                        <CardHeader className="space-y-1">
                            <CardTitle className="text-2xl text-center">Forgot password?</CardTitle>
                            <CardDescription className="text-center">
                                Enter your email and we'll send you a reset link.
                            </CardDescription>
                        </CardHeader>

                        <CardContent>
                            <form onSubmit={handleSubmit} noValidate className="space-y-4">
                                <div className="space-y-2">
                                    <Label htmlFor="email">Email</Label>
                                    <Input
                                        id="email"
                                        name="email"
                                        type="email"
                                        placeholder="john.doe@example.com"
                                        autoComplete="email"
                                        value={form.email}
                                        onChange={handleChange}
                                        aria-invalid={!!errors.email}
                                        className={errors.email ? "border-red-500" : ""}
                                    />
                                    {errors.email && (
                                        <p className="text-sm text-red-500">{errors.email}</p>
                                    )}
                                </div>

                                <Button
                                    type="submit"
                                    className="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700"
                                    disabled={isLoading}
                                >
                                    <Send className="w-4 h-4 mr-2" />
                                    {isLoading ? "Sending…" : "Send reset link"}
                                </Button>
                            </form>
                        </CardContent>
                    </>
                ) : (
                    <>
                        <CardHeader className="space-y-1">
                            <div className="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                <Mail className="w-6 h-6 text-green-600" />
                            </div>
                            <CardTitle className="text-2xl text-center">Check your email</CardTitle>
                            <CardDescription className="text-center">
                                If <span className="font-medium text-slate-700">{form.email}</span> exists
                                in our system, you'll receive a reset link shortly.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <p className="text-sm text-slate-500 text-center">
                                Didn't receive it? Check your spam folder or{" "}
                                <button
                                    type="button"
                                    onClick={() => setSubmitted(false)}
                                    className="text-blue-600 hover:text-blue-700 font-semibold"
                                >
                                    try again
                                </button>
                                .
                            </p>
                        </CardContent>
                    </>
                )}

                <CardFooter>
                    <p className="text-sm text-center w-full text-slate-600">
                        <Link to="/login" className="text-blue-600 hover:text-blue-700 font-semibold">
                            ← Back to sign in
                        </Link>
                    </p>
                </CardFooter>
            </Card>
        </div>
    );
};
