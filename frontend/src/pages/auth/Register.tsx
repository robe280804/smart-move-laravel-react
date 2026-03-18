import { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { Dumbbell, Eye, EyeOff, Sparkles } from "lucide-react";
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
import { registerSchema } from "@/components/forms/authentication";
import type { RegisterFormData, RegisterFormErrors } from "@/types/forms";
import { register } from "@/services/authentication";
import { ApiError } from "@/lib/apiError";
import { notify } from "@/lib/toast";
import { useAuth } from "@/contexts/AuthContext";


export const Register = () => {
    const [form, setForm] = useState<RegisterFormData>({
        name: "",
        surname: "",
        email: "",
        password: "",
        password_confirmation: "",
    });
    const [errors, setErrors] = useState<RegisterFormErrors>({});
    const [isLoading, setIsLoading] = useState(false);
    const [showPassword, setShowPassword] = useState(false);
    const [showConfirmPassword, setShowConfirmPassword] = useState(false);
    const { setSession, isAuthenticated } = useAuth();
    const navigate = useNavigate();


    useEffect(() => {
        if (isAuthenticated) {
            navigate('/dashboard');
        }
    })

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const { name, value } = e.target;
        setForm((prev) => ({ ...prev, [name]: value }));
        if (errors[name as keyof RegisterFormErrors]) {
            setErrors((prev) => ({ ...prev, [name]: undefined }));
        }
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        const result = registerSchema.safeParse(form);
        if (!result.success) {
            const fieldErrors = result.error.flatten().fieldErrors;
            setErrors(
                Object.fromEntries(
                    Object.entries(fieldErrors).map(([k, v]) => [k, v?.[0]])
                ) as RegisterFormErrors
            );
            return;
        }
        setIsLoading(true);
        setErrors({});
        try {
            const response = await register(result.data);
            setSession(response);
            navigate('/dashboard');
        } catch (error: unknown) {
            if (error instanceof ApiError) {
                if (error.fieldErrors) {
                    setErrors(
                        Object.fromEntries(
                            Object.entries(error.fieldErrors ?? {}).map(([k, v]) => [k, v?.[0]])
                        ) as RegisterFormErrors
                    );
                } else {
                    notify.error("Something went wrong. Please try again.");
                }
            }
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <div className="relative z-10 w-full max-w-5xl">
            <div className="grid lg:grid-cols-2 gap-8 items-center">

                {/* Left side — branding (desktop only) */}
                <div className="hidden lg:block space-y-6">
                    <div className="flex items-center gap-3">
                        <div className="w-14 h-14 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                            <Dumbbell className="w-8 h-8 text-white" />
                        </div>
                        <span className="text-3xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                            Smart Move AI
                        </span>
                    </div>
                    <div className="space-y-4">
                        <h1 className="text-4xl font-bold text-slate-900">
                            Start Your Fitness Journey
                        </h1>
                        <p className="text-lg text-slate-600">
                            Join thousands of users achieving their fitness
                            goals with AI-powered personalized plans.
                        </p>
                    </div>
                    <div className="space-y-3 pt-4">
                        <div className="flex items-center gap-3">
                            <div className="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <Sparkles className="w-4 h-4 text-green-600" />
                            </div>
                            <span className="text-slate-700">
                                AI-customized workout plans
                            </span>
                        </div>
                        <div className="flex items-center gap-3">
                            <div className="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <Sparkles className="w-4 h-4 text-blue-600" />
                            </div>
                            <span className="text-slate-700">
                                Progress tracking & analytics
                            </span>
                        </div>
                        <div className="flex items-center gap-3">
                            <div className="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                <Sparkles className="w-4 h-4 text-purple-600" />
                            </div>
                            <span className="text-slate-700">
                                Expert guidance & support
                            </span>
                        </div>
                    </div>
                </div>

                {/* Right side — form */}
                <div>
                    {/* Mobile logo */}
                    <div className="flex lg:hidden items-center justify-center gap-2 mb-6">
                        <div className="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                            <Dumbbell className="w-6 h-6 text-white" />
                        </div>
                        <span className="text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                            Smart Move
                        </span>
                    </div>

                    <Card className="border-0 shadow-2xl">
                        <CardHeader className="space-y-1 pb-4">
                            <CardTitle className="text-xl">Create an account</CardTitle>
                            <CardDescription>
                                Start your personalized fitness journey
                            </CardDescription>
                        </CardHeader>

                        <CardContent className="pb-4">
                            <form onSubmit={handleSubmit} noValidate className="space-y-3">
                                {/* Name + Surname */}
                                <div className="grid grid-cols-2 gap-3">
                                    <div className="space-y-1.5">
                                        <Label htmlFor="name" className="text-sm">Name</Label>
                                        <Input
                                            id="name"
                                            name="name"
                                            placeholder="John"
                                            autoComplete="given-name"
                                            value={form.name}
                                            onChange={handleChange}
                                            aria-invalid={!!errors.name}
                                            className={errors.name ? "border-red-500 h-9" : "h-9"}
                                        />
                                        {errors.name && (
                                            <p className="text-xs text-red-500">{errors.name}</p>
                                        )}
                                    </div>

                                    <div className="space-y-1.5">
                                        <Label htmlFor="surname" className="text-sm">Surname</Label>
                                        <Input
                                            id="surname"
                                            name="surname"
                                            placeholder="Doe"
                                            autoComplete="family-name"
                                            value={form.surname}
                                            onChange={handleChange}
                                            aria-invalid={!!errors.surname}
                                            className={errors.surname ? "border-red-500 h-9" : "h-9"}
                                        />
                                        {errors.surname && (
                                            <p className="text-xs text-red-500">{errors.surname}</p>
                                        )}
                                    </div>
                                </div>

                                {/* Email */}
                                <div className="space-y-1.5">
                                    <Label htmlFor="email" className="text-sm">Email</Label>
                                    <Input
                                        id="email"
                                        name="email"
                                        type="email"
                                        placeholder="john.doe@example.com"
                                        autoComplete="email"
                                        value={form.email}
                                        onChange={handleChange}
                                        aria-invalid={!!errors.email}
                                        className={errors.email ? "border-red-500 h-9" : "h-9"}
                                    />
                                    {errors.email && (
                                        <p className="text-xs text-red-500">{errors.email}</p>
                                    )}
                                </div>

                                {/* Password */}
                                <div className="space-y-1.5">
                                    <Label htmlFor="password" className="text-sm">Password</Label>
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
                                            className={errors.password ? "border-red-500 pr-9 h-9" : "pr-9 h-9"}
                                        />
                                        <button
                                            type="button"
                                            onClick={() => setShowPassword((v) => !v)}
                                            className="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-700"
                                        >
                                            {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                                        </button>
                                    </div>
                                    {errors.password && (
                                        <p className="text-xs text-red-500">{errors.password}</p>
                                    )}
                                </div>

                                {/* Confirm password */}
                                <div className="space-y-1.5">
                                    <Label htmlFor="password_confirmation" className="text-sm">
                                        Confirm Password
                                    </Label>
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
                                            className={errors.password_confirmation ? "border-red-500 pr-9 h-9" : "pr-9 h-9"}
                                        />
                                        <button
                                            type="button"
                                            onClick={() => setShowConfirmPassword((v) => !v)}
                                            className="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-700"
                                        >
                                            {showConfirmPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                                        </button>
                                    </div>
                                    {errors.password_confirmation && (
                                        <p className="text-xs text-red-500">{errors.password_confirmation}</p>
                                    )}
                                </div>

                                <Button
                                    type="submit"
                                    className="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 h-9 mt-4"
                                    disabled={isLoading}
                                >
                                    <Sparkles className="w-4 h-4 mr-2" />
                                    {isLoading ? "Creating account…" : "Create Account"}
                                </Button>
                            </form>
                        </CardContent>

                        <CardFooter className="flex flex-col space-y-3 pt-0">
                            <p className="text-sm text-center text-slate-600">
                                Already have an account?{" "}
                                <Link to="/login" className="text-blue-600 hover:text-blue-700 font-semibold">
                                    Sign in
                                </Link>
                            </p>
                            <p className="text-xs text-center text-slate-500">
                                By creating an account, you agree to our{" "}
                                <a href="#" className="text-blue-600 hover:underline">Terms</a>
                                {" "}and{" "}
                                <a href="#" className="text-blue-600 hover:underline">Privacy Policy</a>
                            </p>
                            <Link to="/" className="text-sm text-slate-600 hover:text-slate-900 mx-auto">
                                ← Back to home
                            </Link>
                        </CardFooter>
                    </Card>
                </div>
            </div>
        </div>
    );
};
