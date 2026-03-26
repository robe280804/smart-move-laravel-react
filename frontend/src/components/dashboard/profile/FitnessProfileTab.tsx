import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { EXPERIENCE_LEVELS, GENDERS } from "@/constants/const";
import type { ExperienceLevel, Gender } from "@/constants/const";
import type { FitnessInfo } from "@/types/user";
import type { FitnessFormState } from "@/hooks/useFitnessForm";
import type { FitnessInfoFormErrors } from "@/types/forms";

interface FitnessProfileTabProps {
    fitnessInfo: FitnessInfo | null;
    form: FitnessFormState;
    errors: FitnessInfoFormErrors;
    isLoading: boolean;
    onFormChange: (form: FitnessFormState) => void;
    onSubmit: (e: React.FormEvent) => void;
}

export function FitnessProfileTab({ fitnessInfo, form, errors, isLoading, onFormChange, onSubmit }: FitnessProfileTabProps) {
    return (
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
                <form onSubmit={onSubmit} className="space-y-4">
                    <div className="grid sm:grid-cols-3 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="height">Height (cm)</Label>
                            <Input
                                id="height"
                                type="number"
                                required
                                value={form.height}
                                onChange={(e) => onFormChange({ ...form, height: e.target.value })}
                                placeholder="e.g. 175"
                            />
                            {errors.height && <p className="text-sm text-red-500">{errors.height}</p>}
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="weight">Weight (kg)</Label>
                            <Input
                                id="weight"
                                type="number"
                                required
                                value={form.weight}
                                onChange={(e) => onFormChange({ ...form, weight: e.target.value })}
                                placeholder="e.g. 70"
                            />
                            {errors.weight && <p className="text-sm text-red-500">{errors.weight}</p>}
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="age">Age</Label>
                            <Input
                                id="age"
                                type="number"
                                required
                                value={form.age}
                                onChange={(e) => onFormChange({ ...form, age: e.target.value })}
                                placeholder="e.g. 25"
                            />
                            {errors.age && <p className="text-sm text-red-500">{errors.age}</p>}
                        </div>
                    </div>
                    <div className="grid sm:grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="gender">Gender</Label>
                            <Select
                                required
                                value={form.gender}
                                onValueChange={(value) => onFormChange({ ...form, gender: value as Gender })}
                            >
                                <SelectTrigger id="gender">
                                    <SelectValue placeholder="Select gender" />
                                </SelectTrigger>
                                <SelectContent>
                                    {GENDERS.map((g) => (
                                        <SelectItem key={g} value={g}>{g.charAt(0).toUpperCase() + g.slice(1)}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.gender && <p className="text-sm text-red-500">{errors.gender}</p>}
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="experience_level">Experience Level</Label>
                            <Select
                                required
                                value={form.experience_level}
                                onValueChange={(value) => onFormChange({ ...form, experience_level: value as ExperienceLevel })}
                            >
                                <SelectTrigger id="experience_level">
                                    <SelectValue placeholder="Select experience level" />
                                </SelectTrigger>
                                <SelectContent>
                                    {EXPERIENCE_LEVELS.map((lvl) => (
                                        <SelectItem key={lvl} value={lvl}>{lvl.charAt(0).toUpperCase() + lvl.slice(1)}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.experience_level && <p className="text-sm text-red-500">{errors.experience_level}</p>}
                        </div>
                    </div>
                    <div className="pt-2">
                        <Button
                            type="submit"
                            className="bg-gradient-to-r from-blue-600 to-indigo-600 cursor-pointer"
                            disabled={isLoading}
                        >
                            {fitnessInfo === null ? "Create Profile" : "Update Profile"}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}
