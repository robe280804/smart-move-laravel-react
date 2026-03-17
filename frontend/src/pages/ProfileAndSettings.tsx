import { useState, useEffect } from "react";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { LogOut, Shield, User, Dumbbell, Settings, Ruler, Weight, Trophy } from "lucide-react";
import { Switch } from "@/components/ui/switch";
import { useAuth } from "@/contexts/AuthContext";
import { getFitnessInfo, storeFitnessInfo, updateFitnessInfo, updatePersonalInfo } from "@/services/user";
import type { FitnessInfo } from "@/types/user";
import { EXPERIENCE_LEVELS, GENDERS } from "@/constants/const";
import type { ExperienceLevel, Gender } from "@/constants/const";
import { fitnessInfoSchema, userProfileShcema } from "@/components/forms/user";
import type { FitnessInfoFormErrors, UserProfileFormErrors } from "@/types/forms";
import { ApiError } from "@/lib/apiError";
import { toast } from "sonner";

export function ProfileAndSettings() {
    const { user, logout, updateUser } = useAuth();
    const [isProfileLoading, setIsProfileLoading] = useState(false);
    const [profileErrors, setProfileErrors] = useState<UserProfileFormErrors>({});
    const [profileForm, setProfileForm] = useState({
        name: user?.name ?? "",
        surname: user?.surname ?? "",
        email: user?.email ?? "",
    });

    const [isFitnessInfoLoading, setIsFitnessInfoLoading] = useState(false);
    const [fitnessInfo, setFitnessInfo] = useState<FitnessInfo | null>(null);
    const [fitnessErrors, setFitnessErrors] = useState<FitnessInfoFormErrors>({});
    const [fitnessForm, setFitnessForm] = useState({
        height: "",
        weight: "",
        age: "",
        gender: "" as Gender | "",
        experience_level: "" as ExperienceLevel | "",
    });

    const [notifications, setNotifications] = useState({
        workoutReminders: true,
        progressUpdates: true,
        achievements: true,
        weeklyReports: false,
    });

    /**
     * Load Fitness Info when page render
     */
    useEffect(() => {
        getFitnessInfo()
            .then((data) => {
                if (!data) {
                    setFitnessInfo(null);
                    return;
                }
                setFitnessInfo(data);
                setFitnessForm({
                    height: data.height ? String(data.height) : "",
                    weight: data.weight ? String(data.weight) : "",
                    age: data.age ? String(data.age) : "",
                    gender: data.gender ?? "",
                    experience_level: data.experience_level ?? "",
                });
            })
            .catch(() => setFitnessInfo(null));
    }, []);

    /**
     * Personal Info form submit
     */
    const handleProfileSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        const result = userProfileShcema.safeParse(profileForm);
        if (!result.success) {
            const fieldErrors = result.error.flatten().fieldErrors;
            setProfileErrors(
                Object.fromEntries(
                    Object.entries(fieldErrors).map(([k, v]) => [k, v?.[0]])
                ) as UserProfileFormErrors
            );
            return;
        }
        setProfileErrors({});
        setIsProfileLoading(true);
        try {
            const updated = await updatePersonalInfo(user!.id, result.data);
            updateUser(updated);
            toast.success("Profile updated.", {
                position: "top-center", duration: 5000,
                style: { background: "#22C55E", color: "#fff" },
            });
        } catch (error: unknown) {
            if (error instanceof ApiError) {
                if (error.fieldErrors) {
                    setProfileErrors(
                        Object.fromEntries(
                            Object.entries(error.fieldErrors).map(([k, v]) => [k, (v as string[])[0]])
                        ) as UserProfileFormErrors
                    );
                } else {
                    toast.error(error.message, {
                        position: "top-center", duration: 5000,
                        style: { background: "#FF4D4F", color: "#fff" },
                    });
                }
            }
        } finally {
            setIsProfileLoading(false);
        }
    };

    /**
     * Fitness Info form submit
     */
    const handleFitnessSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        const result = fitnessInfoSchema.safeParse(fitnessForm);
        if (!result.success) {
            const fieldErrors = result.error.flatten().fieldErrors;
            setFitnessErrors(
                Object.fromEntries(
                    Object.entries(fieldErrors).map(([k, v]) => [k, v?.[0]])
                ) as FitnessInfoFormErrors
            );
            return;
        }
        setFitnessErrors({});
        setIsFitnessInfoLoading(true);
        try {
            if (!fitnessInfo?.id) {
                const created = await storeFitnessInfo(result.data);
                setFitnessInfo(created);
                toast.success("Fitness profile created.", {
                    position: "top-center", duration: 5000,
                    style: { background: "#22C55E", color: "#fff" },
                });
            } else {
                const updated = await updateFitnessInfo(fitnessInfo.id, result.data);
                setFitnessInfo(updated);
                toast.success("Fitness profile updated.", {
                    position: "top-center", duration: 5000,
                    style: { background: "#22C55E", color: "#fff" },
                });
            }
        } catch (error: unknown) {
            if (error instanceof ApiError) {
                if (error.fieldErrors) {
                    setFitnessErrors(
                        Object.fromEntries(
                            Object.entries(error.fieldErrors).map(([k, v]) => [k, (v as string[])[0]])
                        ) as FitnessInfoFormErrors
                    );
                } else {
                    toast.error(error.message, {
                        position: "top-center", duration: 5000,
                        style: { background: "#FF4D4F", color: "#fff" },
                    });
                }
            }
        } finally {
            setIsFitnessInfoLoading(false);
        }
    };

    const initials = user
        ? `${user.name.charAt(0)}${user.surname.charAt(0)}`.toUpperCase()
        : "?";

    const fullName = user ? `${user.name} ${user.surname}` : "";

    return (
        <div className="space-y-6">
            {/* Hero Banner */}
            <div className="relative rounded-2xl overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-900 p-6">
                {/* Decorative blobs */}
                <div className="absolute top-0 right-0 w-64 h-64 bg-indigo-500/10 rounded-full -translate-y-1/2 translate-x-1/4 pointer-events-none" />
                <div className="absolute bottom-0 left-1/3 w-40 h-40 bg-blue-500/10 rounded-full translate-y-1/2 pointer-events-none" />

                <div className="relative flex flex-col sm:flex-row sm:items-center justify-between gap-5">
                    <div>
                        <div className="flex items-center gap-2 mb-2">
                            <div className="w-8 h-8 rounded-lg bg-indigo-500/20 flex items-center justify-center">
                                <Settings className="w-4 h-4 text-indigo-300" />
                            </div>
                            <span className="text-indigo-300 text-xs font-semibold uppercase tracking-widest">
                                Account
                            </span>
                        </div>
                        <h1 className="text-2xl font-bold text-white mb-1">Profile & Settings</h1>
                        <p className="text-slate-400 text-sm">
                            Manage your personal info, fitness data, and preferences
                        </p>
                    </div>

                    <div className="flex items-center gap-3 flex-shrink-0">
                        <div className="relative">
                            <Avatar className="w-14 h-14 ring-2 ring-white/20">
                                <AvatarFallback className="bg-gradient-to-br from-blue-500 to-indigo-600 text-white text-lg font-bold">
                                    {initials}
                                </AvatarFallback>
                            </Avatar>
                        </div>
                        <div>
                            <p className="text-white font-semibold leading-tight">{fullName}</p>
                            <p className="text-slate-400 text-xs">{user?.email}</p>
                        </div>
                    </div>
                </div>

                {/* Stats row */}
                <div className="relative flex flex-wrap items-center gap-6 mt-5 pt-5 border-t border-white/10">
                    {fitnessInfo && (
                        <>
                            <div className="flex items-center gap-2 text-slate-300">
                                <Ruler className="w-4 h-4 text-indigo-400" />
                                <span className="text-sm">
                                    <span className="font-semibold text-white">{fitnessInfo.height}</span>{" "}cm
                                </span>
                            </div>
                            <div className="flex items-center gap-2 text-slate-300">
                                <Weight className="w-4 h-4 text-indigo-400" />
                                <span className="text-sm">
                                    <span className="font-semibold text-white">{fitnessInfo.weight}</span>{" "}kg
                                </span>
                            </div>
                            {fitnessInfo.age && (
                                <div className="flex items-center gap-2 text-slate-300">
                                    <User className="w-4 h-4 text-indigo-400" />
                                    <span className="text-sm">
                                        <span className="font-semibold text-white">{fitnessInfo.age}</span>{" "}years old
                                    </span>
                                </div>
                            )}
                            {fitnessInfo.experience_level && (
                                <div className="flex items-center gap-2 text-slate-300">
                                    <Trophy className="w-4 h-4 text-indigo-400" />
                                    <span className="text-sm font-semibold text-white">
                                        {fitnessInfo.experience_level.charAt(0).toUpperCase() + fitnessInfo.experience_level.slice(1)}
                                    </span>
                                </div>
                            )}
                        </>
                    )}
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={logout}
                        className="ml-auto border-white/20 text-white bg-white/10 hover:bg-red-500/20 hover:border-red-400/40 hover:text-red-300 transition-colors"
                    >
                        <LogOut className="w-4 h-4 mr-2" />
                        Log Out
                    </Button>
                </div>
            </div>

            <div>
                {/* Tabs */}
                <Tabs defaultValue="personal">
                    <TabsList className="grid grid-cols-3 w-full">
                        <TabsTrigger value="personal" className="flex items-center gap-1.5">
                            <User className="w-3.5 h-3.5" />
                            Personal
                        </TabsTrigger>
                        <TabsTrigger value="fitness" className="flex items-center gap-1.5">
                            <Dumbbell className="w-3.5 h-3.5" />
                            Fitness
                        </TabsTrigger>
                        <TabsTrigger value="security" className="flex items-center gap-1.5">
                            <Shield className="w-3.5 h-3.5" />
                            Security
                        </TabsTrigger>
                    </TabsList>

                    {/* Personal Information */}
                    <TabsContent value="personal" className="mt-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>Personal Information</CardTitle>
                                <CardDescription>Update your name and email address</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form onSubmit={handleProfileSubmit} className="space-y-4">
                                    <div className="grid sm:grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="firstName">First Name</Label>
                                            <Input
                                                id="firstName"
                                                value={profileForm.name}
                                                onChange={(e) => setProfileForm({ ...profileForm, name: e.target.value })}
                                                placeholder="Enter your first name"
                                            />
                                            {profileErrors.name && <p className="text-sm text-red-500">{profileErrors.name}</p>}
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="lastName">Last Name</Label>
                                            <Input
                                                id="lastName"
                                                value={profileForm.surname}
                                                onChange={(e) => setProfileForm({ ...profileForm, surname: e.target.value })}
                                                placeholder="Enter your last name"
                                            />
                                            {profileErrors.surname && <p className="text-sm text-red-500">{profileErrors.surname}</p>}
                                        </div>
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="email">Email Address</Label>
                                        <Input
                                            id="email"
                                            type="email"
                                            value={profileForm.email}
                                            onChange={(e) => setProfileForm({ ...profileForm, email: e.target.value })}
                                            placeholder="Enter your email"
                                        />
                                        {profileErrors.email && <p className="text-sm text-red-500">{profileErrors.email}</p>}
                                    </div>
                                    <div className="pt-2">
                                        <Button
                                            type="submit"
                                            className="bg-gradient-to-r from-blue-600 to-indigo-600 cursor-pointer"
                                            disabled={isProfileLoading}
                                        >
                                            Save Changes
                                        </Button>
                                    </div>
                                </form>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Fitness Profile */}
                    <TabsContent value="fitness" className="mt-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>Fitness Profile</CardTitle>
                                <CardDescription>
                                    {fitnessInfo
                                        ? "Update your fitness details to get better recommendations"
                                        : "Complete your fitness profile to get personalized workouts"}
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form onSubmit={handleFitnessSubmit} className="space-y-4">
                                    <div className="grid sm:grid-cols-3 gap-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="height">Height (cm)</Label>
                                            <Input
                                                id="height"
                                                type="number"
                                                required
                                                value={fitnessForm.height}
                                                onChange={(e) => setFitnessForm({ ...fitnessForm, height: e.target.value })}
                                                placeholder="e.g. 175"
                                            />
                                            {fitnessErrors.height && <p className="text-sm text-red-500">{fitnessErrors.height}</p>}
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="weight">Weight (kg)</Label>
                                            <Input
                                                id="weight"
                                                type="number"
                                                required
                                                value={fitnessForm.weight}
                                                onChange={(e) => setFitnessForm({ ...fitnessForm, weight: e.target.value })}
                                                placeholder="e.g. 70"
                                            />
                                            {fitnessErrors.weight && <p className="text-sm text-red-500">{fitnessErrors.weight}</p>}
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="age">Age</Label>
                                            <Input
                                                id="age"
                                                type="number"
                                                required
                                                value={fitnessForm.age}
                                                onChange={(e) => setFitnessForm({ ...fitnessForm, age: e.target.value })}
                                                placeholder="e.g. 25"
                                            />
                                            {fitnessErrors.age && <p className="text-sm text-red-500">{fitnessErrors.age}</p>}
                                        </div>
                                    </div>
                                    <div className="grid sm:grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="gender">Gender</Label>
                                            <select
                                                id="gender"
                                                required
                                                value={fitnessForm.gender}
                                                onChange={(e) => setFitnessForm({ ...fitnessForm, gender: e.target.value as Gender })}
                                                className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                            >
                                                <option value="" disabled>Select gender</option>
                                                {GENDERS.map((g) => (
                                                    <option key={g} value={g}>{g.charAt(0).toUpperCase() + g.slice(1)}</option>
                                                ))}
                                            </select>
                                            {fitnessErrors.gender && <p className="text-sm text-red-500">{fitnessErrors.gender}</p>}
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="experience_level">Experience Level</Label>
                                            <select
                                                id="experience_level"
                                                required
                                                value={fitnessForm.experience_level}
                                                onChange={(e) => setFitnessForm({ ...fitnessForm, experience_level: e.target.value as ExperienceLevel })}
                                                className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                            >
                                                <option value="" disabled>Select experience level</option>
                                                {EXPERIENCE_LEVELS.map((lvl) => (
                                                    <option key={lvl} value={lvl}>{lvl.charAt(0).toUpperCase() + lvl.slice(1)}</option>
                                                ))}
                                            </select>
                                            {fitnessErrors.experience_level && <p className="text-sm text-red-500">{fitnessErrors.experience_level}</p>}
                                        </div>
                                    </div>
                                    <div className="pt-2">
                                        <Button
                                            type="submit"
                                            className="bg-gradient-to-r from-blue-600 to-indigo-600 cursor-pointer"
                                            disabled={isFitnessInfoLoading}
                                        >
                                            {fitnessInfo === null ? "Create Profile" : "Update Profile"}
                                        </Button>
                                    </div>
                                </form>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Security */}
                    <TabsContent value="security" className="mt-4 space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>Change Password</CardTitle>
                                <CardDescription>Update your password to keep your account secure</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-2">
                                    <Label htmlFor="currentPassword">Current Password</Label>
                                    <Input id="currentPassword" type="password" placeholder="Enter current password" />
                                </div>
                                <div className="grid sm:grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="newPassword">New Password</Label>
                                        <Input id="newPassword" type="password" placeholder="Enter new password" />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="confirmPassword">Confirm Password</Label>
                                        <Input id="confirmPassword" type="password" placeholder="Repeat new password" />
                                    </div>
                                </div>
                                <div className="pt-2">
                                    <Button className="bg-gradient-to-r from-blue-600 to-indigo-600">
                                        Update Password
                                    </Button>
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
                    </TabsContent>
                </Tabs>
            </div>
        </div>
    );
}
