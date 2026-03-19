import { useState } from "react";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { CheckCircle, XCircle } from "lucide-react";
import { useAuth } from "@/contexts/AuthContext";
import { resendVerificationEmail } from "@/services/authentication";
import { notify } from "@/lib/toast";
import type { SecurityFormState } from "@/hooks/useSecurityForm";
import type { ChangePasswordFormErrors } from "@/types/forms";

type Props = {
    form: SecurityFormState;
    errors: ChangePasswordFormErrors;
    isLoading: boolean;
    onFormChange: (form: SecurityFormState) => void;
    onSubmit: (e: React.FormEvent) => void;
};

export function SecurityTab({ form, errors, isLoading, onFormChange, onSubmit }: Props) {
    const { user } = useAuth();
    const [isResending, setIsResending] = useState(false);

    const set = (field: keyof SecurityFormState) => (e: React.ChangeEvent<HTMLInputElement>) =>
        onFormChange({ ...form, [field]: e.target.value });

    const handleResend = async () => {
        setIsResending(true);
        try {
            await resendVerificationEmail();
            notify.success("Verification email sent! Check your inbox.");
        } catch {
            notify.error("Failed to send. Please try again later.");
        } finally {
            setIsResending(false);
        }
    };

    return (
        <>
            <Card>
                <CardHeader>
                    <CardTitle>Change Password</CardTitle>
                    <CardDescription>Update your password to keep your account secure</CardDescription>
                </CardHeader>
                <CardContent>
                    <form onSubmit={onSubmit} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="currentPassword">Current Password</Label>
                            <Input
                                id="currentPassword"
                                type="password"
                                placeholder="Enter current password"
                                value={form.current_password}
                                onChange={set("current_password")}
                                disabled={isLoading}
                            />
                            {errors.current_password && (
                                <p className="text-xs text-red-500">{errors.current_password}</p>
                            )}
                        </div>

                        <div className="grid sm:grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="newPassword">New Password</Label>
                                <Input
                                    id="newPassword"
                                    type="password"
                                    placeholder="Enter new password"
                                    value={form.password}
                                    onChange={set("password")}
                                    disabled={isLoading}
                                />
                                {errors.password && (
                                    <p className="text-xs text-red-500">{errors.password}</p>
                                )}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="confirmPassword">Confirm Password</Label>
                                <Input
                                    id="confirmPassword"
                                    type="password"
                                    placeholder="Repeat new password"
                                    value={form.password_confirmation}
                                    onChange={set("password_confirmation")}
                                    disabled={isLoading}
                                />
                                {errors.password_confirmation && (
                                    <p className="text-xs text-red-500">{errors.password_confirmation}</p>
                                )}
                            </div>
                        </div>

                        <div className="pt-2">
                            <Button
                                type="submit"
                                disabled={isLoading}
                                className="bg-gradient-to-r from-blue-600 to-indigo-600"
                            >
                                {isLoading ? "Updating…" : "Update Password"}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Email Verification</CardTitle>
                    <CardDescription>Manage your email verification status</CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="flex items-center justify-between p-4 border rounded-lg">
                        <div className="flex items-center gap-3">
                            {user?.email_verified ? (
                                <CheckCircle className="w-5 h-5 text-emerald-500 flex-shrink-0" />
                            ) : (
                                <XCircle className="w-5 h-5 text-rose-400 flex-shrink-0" />
                            )}
                            <div>
                                <p className="font-medium text-slate-900">
                                    {user?.email_verified ? "Email verified" : "Email not verified"}
                                </p>
                                <p className="text-sm text-slate-500">{user?.email}</p>
                            </div>
                        </div>
                        {!user?.email_verified && (
                            <Button
                                variant="outline"
                                disabled={isResending}
                                onClick={handleResend}
                            >
                                {isResending ? "Sending…" : "Resend email"}
                            </Button>
                        )}
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Account Security</CardTitle>
                    <CardDescription>Additional security options</CardDescription>
                </CardHeader>
                <CardContent className="space-y-3">
                    <div className="flex items-center justify-between p-4 border rounded-lg">
                        <div>
                            <p className="font-medium text-slate-900">Two-Factor Authentication</p>
                            <p className="text-sm text-slate-500">Add an extra layer of security to your account</p>
                        </div>
                        <Button variant="outline">Enable</Button>
                    </div>
                    <div className="flex items-center justify-between p-4 border rounded-lg border-red-200 bg-red-50">
                        <div>
                            <p className="font-medium text-red-900">Delete Account</p>
                            <p className="text-sm text-red-600">Permanently delete your account and all data</p>
                        </div>
                        <Button variant="outline" className="border-red-300 text-red-700 hover:bg-red-100">
                            Delete
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </>
    );
}
