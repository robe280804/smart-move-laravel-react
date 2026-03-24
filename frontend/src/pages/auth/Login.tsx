import { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { Dumbbell, Eye, EyeOff, LogIn } from "lucide-react";
import { Button } from "@/components/ui/button";
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { loginSchema } from "@/components/forms/authentication";
import type { LoginFormData, LoginFormErrors } from "@/types/forms";
import { login } from "@/services/authentication";
import { ApiError } from "@/lib/apiError";
import { notify } from "@/lib/toast";
import { useAuth } from "@/contexts/AuthContext";

export const Login = () => {
    const navigate = useNavigate();
    const [form, setForm] = useState<LoginFormData>({ email: "", password: "" });
    const [errors, setErrors] = useState<LoginFormErrors>({});
    const [isLoading, setIsLoading] = useState(false);
    const [showPassword, setShowPassword] = useState(false);
    const { setSession, isAuthenticated } = useAuth();

    useEffect(() => {
        if (isAuthenticated) {
            navigate('/dashboard');
        }
    });

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const { name, value } = e.target;
        setForm((prev) => ({ ...prev, [name]: value }));
        if (errors[name as keyof LoginFormErrors]) {
            setErrors((prev) => ({ ...prev, [name]: undefined }));
        }
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (isLoading) return;
        const result = loginSchema.safeParse(form);
        if (!result.success) {
            const fieldErrors = result.error.flatten().fieldErrors;
            setErrors(
                Object.fromEntries(
                    Object.entries(fieldErrors).map(([k, v]) => [k, v?.[0]])
                ) as LoginFormErrors
            );
            return;
        }
        setIsLoading(true);
        setErrors({});
        try {
            const response = await login(result.data);
            setSession(response);
            navigate("/dashboard");
        } catch (error: unknown) {
            if (error instanceof ApiError) {
                if (error.fieldErrors) {
                    setErrors(
                        Object.fromEntries(
                            Object.entries(error.fieldErrors).map(([k, v]) => [k, v[0]])
                        ) as LoginFormErrors
                    );
                } else {
                    notify.error(error.message);
                }
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
                <CardHeader className="space-y-1">
                    <CardTitle className="text-2xl text-center">Welcome back</CardTitle>
                    <CardDescription className="text-center">
                        Sign in to continue your fitness journey
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <form onSubmit={handleSubmit} noValidate className="space-y-4">
                        {/* Email */}
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

                        {/* Password */}
                        <div className="space-y-2">
                            <div className="flex items-center justify-between">
                                <Label htmlFor="password">Password</Label>
                                <a href="/forgot-password" className="text-sm text-blue-600 hover:text-blue-700">
                                    Forgot password?
                                </a>
                            </div>
                            <div className="relative">
                                <Input
                                    id="password"
                                    name="password"
                                    type={showPassword ? "text" : "password"}
                                    placeholder="••••••••"
                                    autoComplete="current-password"
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

                        <Button
                            type="submit"
                            className="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700"
                            disabled={isLoading}
                        >
                            <LogIn className="w-4 h-4 mr-2" />
                            {isLoading ? "Signing in…" : "Sign In"}
                        </Button>
                    </form>

                </CardContent>

                <CardFooter className="flex flex-col gap-3">
                    <p className="text-sm text-center w-full text-slate-600">
                        Don't have an account?{" "}
                        <Link to="/register" className="text-blue-600 hover:text-blue-700 font-semibold">
                            Sign up
                        </Link>
                    </p>
                    <p className="text-xs text-center text-slate-500">
                        By signing in, you agree to our{" "}
                        <Link to="/terms" className="text-blue-600 hover:underline">Terms</Link>
                        {" "}and{" "}
                        <Link to="/privacy" className="text-blue-600 hover:underline">Privacy Policy</Link>
                    </p>
                </CardFooter>
            </Card>

            <div className="mt-6 text-center">
                <Link to="/" className="text-sm text-slate-600 hover:text-slate-900">
                    ← Back to home
                </Link>
            </div>
        </div>
    );
};
