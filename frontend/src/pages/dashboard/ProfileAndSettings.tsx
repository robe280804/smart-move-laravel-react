import { useState, useEffect } from "react";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { LogOut, Camera } from "lucide-react";
import { Switch } from "@/components/ui/switch";
import { useAuth } from "@/contexts/AuthContext";
import { getFitnessInfo, storeFitnessInfo, updateFitnessInfo } from "@/services/user";
import type { FitnessInfo } from "@/types/user";
import { EXPERIENCE_LEVELS, GENDERS } from "@/constants/const";
import type { ExperienceLevel, Gender } from "@/constants/const";
import { ApiError } from "@/lib/apiError";
import { toast } from "sonner";

export function ProfileAndSettings() {
    const { user, logout } = useAuth();
    const [isFitnessInfoLoading, setIsFitnessInfoLoading] = useState(false);
    const [fitnessInfo, setFitnessInfo] = useState<FitnessInfo | null>(null);
    const [fitnessErrors, setFitnessErrors] = useState<Record<string, string>>({});
    const [fitnessForm, setFitnessForm] = useState({
        height: "",
        weight: "",
        age: "",
        gender: "" as Gender | "",
        experience_level: "" as ExperienceLevel | "",
    });

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

    const handleFitnessSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setFitnessErrors({});
        setIsFitnessInfoLoading(true);
        const payload = {
            height: Number(fitnessForm.height),
            weight: Number(fitnessForm.weight),
            age: Number(fitnessForm.age),
            gender: fitnessForm.gender as Gender,
            experience_level: fitnessForm.experience_level as ExperienceLevel,
        };
        try {
            if (!fitnessInfo?.id) {
                const created = await storeFitnessInfo(payload);
                setFitnessInfo(created);
                toast.success("Fitness profile created.");
            } else {
                const updated = await updateFitnessInfo(fitnessInfo.id, payload);
                setFitnessInfo(updated);
                toast.success("Fitness profile updated.");
            }
        } catch (error: unknown) {
            if (error instanceof ApiError) {
                if (error.fieldErrors) {
                    setFitnessErrors(
                        Object.fromEntries(
                            Object.entries(error.fieldErrors).map(([k, v]) => [k, (v as string[])[0]])
                        )
                    );
                } else {
                    toast.error(error.message);
                }
            }
        } finally {
            setIsFitnessInfoLoading(false);
        }
    };

    const [notifications, setNotifications] = useState({
        workoutReminders: true,
        progressUpdates: true,
        achievements: true,
        weeklyReports: false,
    });

    const initials = user
        ? `${user.name.charAt(0)}${user.surname.charAt(0)}`.toUpperCase()
        : "?";

    const fullName = user ? `${user.name} ${user.surname}` : "";

    return (
        <div className="space-y-6">
            <div>
                <h1 className="text-3xl font-bold text-slate-900">Profile & Settings</h1>
                <p className="text-slate-600 mt-1">Manage your account and preferences</p>
            </div>

            {/* Profile Header */}
            <Card>
                <CardContent className="p-6">
                    <div className="flex items-center gap-6">
                        <div className="relative">
                            <Avatar className="w-24 h-24">
                                <AvatarFallback className="bg-gradient-to-br from-blue-600 to-indigo-600 text-white text-2xl font-bold">
                                    {initials}
                                </AvatarFallback>
                            </Avatar>
                            <button className="absolute bottom-0 right-0 w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white hover:bg-blue-700 shadow-lg">
                                <Camera className="w-4 h-4" />
                            </button>
                        </div>
                        <div className="flex-1">
                            <h2 className="text-2xl font-bold text-slate-900">{fullName}</h2>
                            <p className="text-slate-600">{user?.email}</p>
                            <div className="flex gap-2 mt-3">
                                <Button variant="outline" size="sm" onClick={logout}>
                                    <LogOut className="w-4 h-4 mr-2" />
                                    Log Out
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Tabs defaultValue="personal">
                <TabsList>
                    <TabsTrigger value="personal">Personal Info</TabsTrigger>
                    <TabsTrigger value="notifications">Notifications</TabsTrigger>
                    <TabsTrigger value="security">Security</TabsTrigger>
                </TabsList>

                <TabsContent value="personal" className="space-y-6 mt-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Personal Information</CardTitle>
                            <CardDescription>Update your personal details</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="firstName">First Name</Label>
                                    <Input id="firstName" defaultValue={user?.name ?? ""} />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="lastName">Last Name</Label>
                                    <Input id="lastName" defaultValue={user?.surname ?? ""} />
                                </div>
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="email">Email</Label>
                                <Input id="email" type="email" defaultValue={user?.email ?? ""} />
                            </div>
                            <Button className="bg-gradient-to-r from-blue-600 to-indigo-600">
                                Save Changes
                            </Button>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Fitness Profile</CardTitle>
                            <CardDescription>Your fitness goals and preferences</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={handleFitnessSubmit} className="space-y-4">
                                <div className="grid md:grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="height">Height (cm)</Label>
                                        <Input
                                            id="height"
                                            type="number"
                                            required
                                            value={fitnessForm.height}
                                            onChange={(e) => setFitnessForm({ ...fitnessForm, height: e.target.value })}
                                            placeholder="Enter your height"
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
                                            placeholder="Enter your weight"
                                        />
                                        {fitnessErrors.weight && <p className="text-sm text-red-500">{fitnessErrors.weight}</p>}
                                    </div>
                                </div>
                                <div className="grid md:grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="age">Age</Label>
                                        <Input
                                            id="age"
                                            type="number"
                                            required
                                            value={fitnessForm.age}
                                            onChange={(e) => setFitnessForm({ ...fitnessForm, age: e.target.value })}
                                            placeholder="Enter your age"
                                        />
                                        {fitnessErrors.age && <p className="text-sm text-red-500">{fitnessErrors.age}</p>}
                                    </div>
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
                                <Button type="submit" className="bg-gradient-to-r from-blue-600 to-indigo-600 cursor-pointer" disabled={isFitnessInfoLoading}>
                                    {fitnessInfo === null ? "Create Profile" : "Update Profile"}
                                </Button>
                            </form>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="notifications" className="space-y-6 mt-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Notification Preferences</CardTitle>
                            <CardDescription>Choose what updates you want to receive</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            <div className="flex items-center justify-between">
                                <div className="space-y-0.5">
                                    <p className="font-medium text-slate-900">Workout Reminders</p>
                                    <p className="text-sm text-slate-600">Get notified before scheduled workouts</p>
                                </div>
                                <Switch
                                    checked={notifications.workoutReminders}
                                    onCheckedChange={(checked) =>
                                        setNotifications({ ...notifications, workoutReminders: checked })
                                    }
                                />
                            </div>

                            <div className="flex items-center justify-between">
                                <div className="space-y-0.5">
                                    <p className="font-medium text-slate-900">Progress Updates</p>
                                    <p className="text-sm text-slate-600">Daily summaries of your progress</p>
                                </div>
                                <Switch
                                    checked={notifications.progressUpdates}
                                    onCheckedChange={(checked) =>
                                        setNotifications({ ...notifications, progressUpdates: checked })
                                    }
                                />
                            </div>

                            <div className="flex items-center justify-between">
                                <div className="space-y-0.5">
                                    <p className="font-medium text-slate-900">Achievements</p>
                                    <p className="text-sm text-slate-600">Celebrate your milestones and badges</p>
                                </div>
                                <Switch
                                    checked={notifications.achievements}
                                    onCheckedChange={(checked) =>
                                        setNotifications({ ...notifications, achievements: checked })
                                    }
                                />
                            </div>

                            <div className="flex items-center justify-between">
                                <div className="space-y-0.5">
                                    <p className="font-medium text-slate-900">Weekly Reports</p>
                                    <p className="text-sm text-slate-600">Comprehensive weekly fitness summaries</p>
                                </div>
                                <Switch
                                    checked={notifications.weeklyReports}
                                    onCheckedChange={(checked) =>
                                        setNotifications({ ...notifications, weeklyReports: checked })
                                    }
                                />
                            </div>

                            <Button className="bg-gradient-to-r from-blue-600 to-indigo-600">
                                Save Preferences
                            </Button>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="security" className="space-y-6 mt-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Change Password</CardTitle>
                            <CardDescription>Update your password to keep your account secure</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="currentPassword">Current Password</Label>
                                <Input id="currentPassword" type="password" />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="newPassword">New Password</Label>
                                <Input id="newPassword" type="password" />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="confirmPassword">Confirm New Password</Label>
                                <Input id="confirmPassword" type="password" />
                            </div>
                            <Button className="bg-gradient-to-r from-blue-600 to-indigo-600">
                                Update Password
                            </Button>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Account Security</CardTitle>
                            <CardDescription>Additional security options</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="flex items-center justify-between p-4 border rounded-lg">
                                <div>
                                    <p className="font-medium text-slate-900">Two-Factor Authentication</p>
                                    <p className="text-sm text-slate-600">Add an extra layer of security</p>
                                </div>
                                <Button variant="outline">Enable</Button>
                            </div>
                            <div className="flex items-center justify-between p-4 border rounded-lg border-red-200 bg-red-50">
                                <div>
                                    <p className="font-medium text-red-900">Delete Account</p>
                                    <p className="text-sm text-red-700">Permanently delete your account and data</p>
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
    );
}
