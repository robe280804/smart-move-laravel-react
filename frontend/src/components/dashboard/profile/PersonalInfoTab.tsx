import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import type { ProfileFormState } from "@/hooks/useProfileForm";
import type { UserProfileFormErrors } from "@/types/forms";

interface PersonalInfoTabProps {
    form: ProfileFormState;
    errors: UserProfileFormErrors;
    isLoading: boolean;
    onFormChange: (form: ProfileFormState) => void;
    onSubmit: (e: React.FormEvent) => void;
}

export function PersonalInfoTab({ form, errors, isLoading, onFormChange, onSubmit }: PersonalInfoTabProps) {
    return (
        <Card>
            <CardHeader>
                <CardTitle>Personal Information</CardTitle>
                <CardDescription>Update your name and email address</CardDescription>
            </CardHeader>
            <CardContent>
                <form onSubmit={onSubmit} className="space-y-4">
                    <div className="grid sm:grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="firstName">First Name</Label>
                            <Input
                                id="firstName"
                                value={form.name}
                                onChange={(e) => onFormChange({ ...form, name: e.target.value })}
                                placeholder="Enter your first name"
                            />
                            {errors.name && <p className="text-sm text-red-500">{errors.name}</p>}
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="lastName">Last Name</Label>
                            <Input
                                id="lastName"
                                value={form.surname}
                                onChange={(e) => onFormChange({ ...form, surname: e.target.value })}
                                placeholder="Enter your last name"
                            />
                            {errors.surname && <p className="text-sm text-red-500">{errors.surname}</p>}
                        </div>
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor="email">Email Address</Label>
                        <Input
                            id="email"
                            type="email"
                            value={form.email}
                            onChange={(e) => onFormChange({ ...form, email: e.target.value })}
                            placeholder="Enter your email"
                        />
                        {errors.email && <p className="text-sm text-red-500">{errors.email}</p>}
                    </div>
                    <div className="pt-2">
                        <Button
                            type="submit"
                            className="bg-gradient-to-r from-blue-600 to-indigo-600 cursor-pointer"
                            disabled={isLoading}
                        >
                            Save Changes
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}
