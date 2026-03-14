import { useState } from "react";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Badge } from "@/components/ui/badge";
import {
    Brain,
    Sparkles,
    ArrowRight,
    Check,
    ChevronRight,
    Loader2,
    ChevronDown,
    X
} from "lucide-react";
import { Checkbox } from "@/components/ui/checkbox";
import { DAYS_OF_WEEK, FITNESS_GOALS, EQUIPMENT_OPTIONS, WORKOUT_TYPES } from "@/constants/const";
import type { MessageType, WorkoutPlanData } from "@/types/workout";
import { toast } from "sonner";

export function WorkoutPlanGenerator() {
    const [step, setStep] = useState(0);
    const [showAllGoals, setShowAllGoals] = useState(false);
    const [messages, setMessages] = useState<MessageType[]>([
        {
            id: "1",
            role: "assistant",
            content: "Hello! I'm your AI fitness coach. I'll help you create a personalized workout plan tailored to your goals, schedule, and preferences. Let's get started! 🎯",
            timestamp: new Date()
        }
    ]);
    const [isGenerating, setIsGenerating] = useState(false);
    const [planData, setPlanData] = useState<WorkoutPlanData>({
        fitnessGoals: [],
        trainingDaysPerWeek: 3,
        availableDays: [],
        sessionDuration: 60,
        restDays: 2,
        injuries: "",
        equipment: [],
        gymAccess: false,
        workoutType: []
    });

    const addMessage = (role: "user" | "assistant", content: string) => {
        const newMessage: MessageType = {
            id: Date.now().toString(),
            role,
            content,
            timestamp: new Date()
        };
        setMessages(prev => [...prev, newMessage]);
    };

    const handleGoalToggle = (goal: string) => {
        const isSelected = planData.fitnessGoals.includes(goal);
        if (isSelected) {
            setPlanData({ ...planData, fitnessGoals: planData.fitnessGoals.filter(g => g !== goal) });
        } else {
            if (planData.fitnessGoals.length >= 3) return;
            setPlanData({ ...planData, fitnessGoals: [...planData.fitnessGoals, goal] });
        }
    };

    const handleGoals = () => {
        if (planData.fitnessGoals.length === 0) {
            alert("Please select at least one goal");
            return;
        }

        const goalLabels = planData.fitnessGoals
            .map(g => FITNESS_GOALS.find(fg => fg.value === g)?.label)
            .join(", ");
        addMessage("user", `My goals: ${goalLabels}`);

        setTimeout(() => {
            addMessage("assistant", "Great choice! Now, let's talk about your schedule. How many days per week can you commit to training?");
            setStep(1);
        }, 500);
    };

    const handleSchedule = () => {
        if (planData.availableDays.length === 0) {
            toast.info("Please select at least one available day", {
                position: "top-center",
                duration: 5000,
                style: {
                    background: "#3B82F6",
                    color: "#fff",
                },
            });
            return;
        }

        addMessage("user", `I can train ${planData.trainingDaysPerWeek} days per week on ${planData.availableDays.join(", ")} for ${planData.sessionDuration} minutes per session.`);

        setTimeout(() => {
            addMessage("assistant", "Perfect! Do you have any injuries or physical limitations I should be aware of? This helps me create a safe and effective plan.");
            setStep(2);
        }, 500);
    };

    const handleConstraints = () => {
        const injuryText = planData.injuries.trim() || "No injuries or limitations";
        addMessage("user", `${injuryText}. I prefer ${planData.restDays} rest days per week.`);

        setTimeout(() => {
            addMessage("assistant", "Thanks for sharing that. What equipment do you have access to? Select all that apply.");
            setStep(3);
        }, 500);
    };

    const handleEquipment = () => {
        if (planData.equipment.length === 0) {
            alert("Please select at least one equipment option");
            return;
        }

        const equipmentText = planData.equipment.join(", ");
        const gymText = planData.gymAccess ? "I have gym access" : "Home workout";
        addMessage("user", `${gymText}. Available equipment: ${equipmentText}`);

        setTimeout(() => {
            addMessage("assistant", "Almost done! What type of workouts do you prefer? You can select multiple options.");
            setStep(4);
        }, 500);
    };

    const handlePreferences = () => {
        if (planData.workoutType.length === 0) {
            alert("Please select at least one workout type");
            return;
        }

        const typeLabels = planData.workoutType.map(type =>
            WORKOUT_TYPES.find(t => t.value === type)?.label
        ).join(", ");

        addMessage("user", `I prefer: ${typeLabels}`);

        setTimeout(() => {
            addMessage("assistant", "Excellent! I have all the information I need. Let me generate your personalized workout plan...");
            setStep(5);
            generatePlan();
        }, 500);
    };

    const generatePlan = () => {
        setIsGenerating(true);

        // Simulate AI generation
        setTimeout(() => {
            const goalLabels = planData.fitnessGoals
                .map(g => FITNESS_GOALS.find(fg => fg.value === g)?.label)
                .join(", ");
            const planSummary = `
🎯 **Your Personalized ${planData.trainingDaysPerWeek}-Day Workout Plan**

**Goal${planData.fitnessGoals.length > 1 ? "s" : ""}:** ${goalLabels}
**Schedule:** ${planData.availableDays.join(", ")} • ${planData.sessionDuration} min/session
**Equipment:** ${planData.equipment.join(", ")}
**Focus:** ${planData.workoutType.map(t => WORKOUT_TYPES.find(wt => wt.value === t)?.label).join(", ")}

Your plan has been generated successfully! It includes:
✅ Progressive overload structure
✅ Personalized exercise selection
✅ Recovery optimization
✅ AI-adjusted intensity levels

Would you like to view your complete workout plan or save it to your dashboard?
    `.trim();

            addMessage("assistant", planSummary);
            setIsGenerating(false);
            setStep(6);
        }, 3000);
    };

    const handleReset = () => {
        setStep(0);
        setMessages([{
            id: "1",
            role: "assistant",
            content: "Hello! I'm your AI fitness coach. I'll help you create a personalized workout plan tailored to your goals, schedule, and preferences. Let's get started! 🎯",
            timestamp: new Date()
        }]);
        setPlanData({
            fitnessGoals: [],
            trainingDaysPerWeek: 3,
            availableDays: [],
            sessionDuration: 60,
            restDays: 2,
            injuries: "",
            equipment: [],
            gymAccess: false,
            workoutType: []
        });
    };

    return (
        <div className="space-y-6">
            {/* Header */}
            <div>
                <h1 className="text-3xl font-bold text-slate-900">AI Workout Plan Generator</h1>
                <p className="text-slate-600 mt-1">Create a personalized workout plan with AI assistance</p>
            </div>

            {/* Progress Steps */}
            <Card>
                <CardContent className="p-6">
                    <div className="flex items-center justify-between">
                        {["Goal", "Schedule", "Constraints", "Equipment", "Preferences", "Generate"].map((label, index) => (
                            <div key={index} className="flex items-center">
                                <div className="flex flex-col items-center">
                                    <div className={`w-10 h-10 rounded-full flex items-center justify-center font-semibold ${index < step
                                        ? "bg-green-600 text-white"
                                        : index === step
                                            ? "bg-blue-600 text-white"
                                            : "bg-slate-200 text-slate-600"
                                        }`}>
                                        {index < step ? <Check className="w-5 h-5" /> : index + 1}
                                    </div>
                                    <span className={`text-xs mt-2 font-medium ${index <= step ? "text-slate-900" : "text-slate-400"
                                        }`}>
                                        {label}
                                    </span>
                                </div>
                                {index < 5 && (
                                    <div className={`h-0.5 w-12 mx-2 ${index < step ? "bg-green-600" : "bg-slate-200"
                                        }`} />
                                )}
                            </div>
                        ))}
                    </div>
                </CardContent>
            </Card>

            <div className="grid lg:grid-cols-3 gap-6">
                {/* Chat Area */}
                <Card className="lg:col-span-2 flex flex-col">
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-2">
                                <div className="w-10 h-10 bg-gradient-to-br from-purple-600 to-indigo-600 rounded-full flex items-center justify-center">
                                    <Brain className="w-6 h-6 text-white" />
                                </div>
                                <div>
                                    <CardTitle>AI Fitness Coach</CardTitle>
                                    <CardDescription>Answer questions to build your plan</CardDescription>
                                </div>
                            </div>
                            {step > 0 && step < 6 && (
                                <Button variant="ghost" size="sm" onClick={handleReset}>
                                    Start Over
                                </Button>
                            )}
                        </div>
                    </CardHeader>
                    <CardContent className="flex flex-col flex-1 min-h-0 p-6">
                        {/* Messages */}
                        <div className="space-y-4 mb-6 flex-1 overflow-y-auto min-h-0 max-h-72">
                            {messages.map((message) => (
                                <div
                                    key={message.id}
                                    className={`flex gap-3 ${message.role === "user" ? "justify-end" : ""}`}
                                >
                                    {message.role === "assistant" && (
                                        <div className="w-8 h-8 bg-gradient-to-br from-purple-600 to-indigo-600 rounded-full flex items-center justify-center flex-shrink-0">
                                            <Sparkles className="w-4 h-4 text-white" />
                                        </div>
                                    )}
                                    <div
                                        className={`max-w-[80%] p-4 rounded-2xl ${message.role === "user"
                                            ? "bg-gradient-to-r from-blue-600 to-indigo-600 text-white"
                                            : "bg-slate-100 text-slate-900"
                                            }`}
                                    >
                                        <p className="whitespace-pre-line">{message.content}</p>
                                    </div>
                                    {message.role === "user" && (
                                        <div className="w-8 h-8 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-full flex items-center justify-center flex-shrink-0 text-white font-semibold text-sm">
                                            JD
                                        </div>
                                    )}
                                </div>
                            ))}
                            {isGenerating && (
                                <div className="flex gap-3">
                                    <div className="w-8 h-8 bg-gradient-to-br from-purple-600 to-indigo-600 rounded-full flex items-center justify-center flex-shrink-0">
                                        <Loader2 className="w-4 h-4 text-white animate-spin" />
                                    </div>
                                    <div className="max-w-[80%] p-4 rounded-2xl bg-slate-100">
                                        <p className="text-slate-600">Generating your personalized workout plan...</p>
                                    </div>
                                </div>
                            )}
                        </div>

                        {/* Input Area Based on Step */}
                        {step === 0 && (
                            <div className="space-y-3">
                                <div className="flex items-center justify-between">
                                    <Label>What are your fitness goals? (select 1–3)</Label>
                                    {planData.fitnessGoals.length > 0 && (
                                        <span className="text-xs text-blue-600 font-medium">{planData.fitnessGoals.length}/3 selected</span>
                                    )}
                                </div>
                                <div className="grid md:grid-cols-2 gap-3">
                                    {FITNESS_GOALS.slice(0, 4).map((goal) => {
                                        const isSelected = planData.fitnessGoals.includes(goal.value);
                                        return (
                                            <button
                                                key={goal.value}
                                                onClick={() => handleGoalToggle(goal.value)}
                                                className={`p-4 border-2 rounded-xl transition-all text-left ${isSelected
                                                    ? "border-blue-600 bg-blue-50"
                                                    : planData.fitnessGoals.length >= 3
                                                        ? "border-slate-200 opacity-50 cursor-not-allowed"
                                                        : "border-slate-200 hover:border-blue-600 hover:bg-blue-50"
                                                    }`}
                                            >
                                                <div className="flex items-start gap-3">
                                                    <span className="text-2xl">{goal.icon}</span>
                                                    <div className="flex-1">
                                                        <p className={`font-semibold ${isSelected ? "text-blue-600" : "text-slate-900"}`}>
                                                            {goal.label}
                                                        </p>
                                                        <p className="text-sm text-slate-600">{goal.description}</p>
                                                    </div>
                                                    {isSelected && <Check className="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />}
                                                </div>
                                            </button>
                                        );
                                    })}
                                </div>
                                <Button
                                    variant="outline"
                                    className="w-full"
                                    onClick={() => setShowAllGoals(true)}
                                >
                                    View all goals <ChevronDown className="w-4 h-4 ml-2" />
                                </Button>
                                <Button
                                    onClick={handleGoals}
                                    className="w-full bg-gradient-to-r from-blue-600 to-indigo-600"
                                    disabled={planData.fitnessGoals.length === 0}
                                >
                                    Continue <ArrowRight className="w-4 h-4 ml-2" />
                                </Button>
                            </div>
                        )}

                        {step === 1 && (
                            <div className="space-y-4">
                                <div className="grid md:grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="trainingDays">Training days per week</Label>
                                        <Input
                                            id="trainingDays"
                                            type="number"
                                            min="1"
                                            max="7"
                                            value={planData.trainingDaysPerWeek}
                                            onChange={(e) => setPlanData({ ...planData, trainingDaysPerWeek: parseInt(e.target.value) })}
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="sessionDuration">Session duration (minutes)</Label>
                                        <Input
                                            id="sessionDuration"
                                            type="number"
                                            min="15"
                                            max="180"
                                            value={planData.sessionDuration}
                                            onChange={(e) => setPlanData({ ...planData, sessionDuration: parseInt(e.target.value) })}
                                        />
                                    </div>
                                </div>
                                <div className="space-y-2">
                                    <Label>Available days of the week</Label>
                                    <div className="grid grid-cols-2 md:grid-cols-4 gap-2">
                                        {DAYS_OF_WEEK.map((day) => (
                                            <label
                                                key={day}
                                                className={`flex items-center gap-2 p-3 border-2 rounded-lg cursor-pointer transition-all ${planData.availableDays.includes(day)
                                                    ? "border-blue-600 bg-blue-50"
                                                    : "border-slate-200 hover:border-slate-300"
                                                    }`}
                                            >
                                                <Checkbox
                                                    checked={planData.availableDays.includes(day)}
                                                    onCheckedChange={(checked) => {
                                                        if (checked) {
                                                            setPlanData({ ...planData, availableDays: [...planData.availableDays, day] });
                                                        } else {
                                                            setPlanData({ ...planData, availableDays: planData.availableDays.filter(d => d !== day) });
                                                        }
                                                    }}
                                                />
                                                <span className="text-sm font-medium">{day.slice(0, 3)}</span>
                                            </label>
                                        ))}
                                    </div>
                                </div>
                                <Button
                                    onClick={handleSchedule}
                                    className="w-full bg-gradient-to-r from-blue-600 to-indigo-600"
                                >
                                    Continue <ArrowRight className="w-4 h-4 ml-2" />
                                </Button>
                            </div>
                        )}

                        {step === 2 && (
                            <div className="space-y-4">
                                <div className="space-y-2">
                                    <Label htmlFor="injuries">Injuries or limitations (optional)</Label>
                                    <Input
                                        id="injuries"
                                        placeholder="e.g., Lower back pain, knee injury, etc."
                                        value={planData.injuries}
                                        onChange={(e) => setPlanData({ ...planData, injuries: e.target.value })}
                                    />
                                    <p className="text-xs text-slate-500">Leave blank if none</p>
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="restDays">Preferred rest days per week</Label>
                                    <Input
                                        id="restDays"
                                        type="number"
                                        min="0"
                                        max="7"
                                        value={planData.restDays}
                                        onChange={(e) => setPlanData({ ...planData, restDays: parseInt(e.target.value) })}
                                    />
                                </div>
                                <Button
                                    onClick={handleConstraints}
                                    className="w-full bg-gradient-to-r from-blue-600 to-indigo-600"
                                >
                                    Continue <ArrowRight className="w-4 h-4 ml-2" />
                                </Button>
                            </div>
                        )}

                        {step === 3 && (
                            <div className="space-y-4">
                                <div className="flex items-center gap-2 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                    <Checkbox
                                        id="gymAccess"
                                        checked={planData.gymAccess}
                                        onCheckedChange={(checked) => setPlanData({ ...planData, gymAccess: checked as boolean })}
                                    />
                                    <Label htmlFor="gymAccess" className="cursor-pointer">
                                        I have gym access
                                    </Label>
                                </div>
                                <div className="space-y-2">
                                    <Label>Available equipment (select all that apply)</Label>
                                    <div className="grid md:grid-cols-2 gap-2">
                                        {EQUIPMENT_OPTIONS.map((equipment) => (
                                            <label
                                                key={equipment}
                                                className={`flex items-center gap-2 p-3 border-2 rounded-lg cursor-pointer transition-all ${planData.equipment.includes(equipment)
                                                    ? "border-blue-600 bg-blue-50"
                                                    : "border-slate-200 hover:border-slate-300"
                                                    }`}
                                            >
                                                <Checkbox
                                                    checked={planData.equipment.includes(equipment)}
                                                    onCheckedChange={(checked) => {
                                                        if (checked) {
                                                            setPlanData({ ...planData, equipment: [...planData.equipment, equipment] });
                                                        } else {
                                                            setPlanData({ ...planData, equipment: planData.equipment.filter(e => e !== equipment) });
                                                        }
                                                    }}
                                                />
                                                <span className="text-sm font-medium">{equipment}</span>
                                            </label>
                                        ))}
                                    </div>
                                </div>
                                <Button
                                    onClick={handleEquipment}
                                    className="w-full bg-gradient-to-r from-blue-600 to-indigo-600"
                                >
                                    Continue <ArrowRight className="w-4 h-4 ml-2" />
                                </Button>
                            </div>
                        )}

                        {step === 4 && (
                            <div className="space-y-4">
                                <div className="space-y-2">
                                    <Label>Preferred workout types (select all that apply)</Label>
                                    <div className="grid md:grid-cols-2 gap-3">
                                        {WORKOUT_TYPES.map((type) => (
                                            <label
                                                key={type.value}
                                                className={`flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer transition-all ${planData.workoutType.includes(type.value)
                                                    ? "border-blue-600 bg-blue-50"
                                                    : "border-slate-200 hover:border-slate-300"
                                                    }`}
                                            >
                                                <Checkbox
                                                    checked={planData.workoutType.includes(type.value)}
                                                    onCheckedChange={(checked) => {
                                                        if (checked) {
                                                            setPlanData({ ...planData, workoutType: [...planData.workoutType, type.value] });
                                                        } else {
                                                            setPlanData({ ...planData, workoutType: planData.workoutType.filter(t => t !== type.value) });
                                                        }
                                                    }}
                                                />
                                                <span className="text-2xl">{type.icon}</span>
                                                <span className="font-medium">{type.label}</span>
                                            </label>
                                        ))}
                                    </div>
                                </div>
                                <Button
                                    onClick={handlePreferences}
                                    className="w-full bg-gradient-to-r from-blue-600 to-indigo-600"
                                >
                                    Generate My Plan <Sparkles className="w-4 h-4 ml-2" />
                                </Button>
                            </div>
                        )}

                        {step === 6 && (
                            <div className="space-y-3">
                                <Button className="w-full bg-gradient-to-r from-blue-600 to-indigo-600">
                                    View Complete Plan <ChevronRight className="w-4 h-4 ml-2" />
                                </Button>
                                <Button variant="outline" className="w-full" onClick={handleReset}>
                                    Generate Another Plan
                                </Button>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Info Sidebar */}
                <div className="space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-lg">Your Information</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {planData.fitnessGoals.length > 0 && (
                                <div>
                                    <p className="text-sm text-slate-600 mb-1">Fitness Goals</p>
                                    <div className="flex flex-wrap gap-1">
                                        {planData.fitnessGoals.map(g => (
                                            <Badge key={g} className="bg-blue-600">
                                                {FITNESS_GOALS.find(fg => fg.value === g)?.label}
                                            </Badge>
                                        ))}
                                    </div>
                                </div>
                            )}
                            {planData.availableDays.length > 0 && (
                                <div>
                                    <p className="text-sm text-slate-600 mb-1">Schedule</p>
                                    <p className="text-sm font-medium text-slate-900">
                                        {planData.trainingDaysPerWeek} days/week • {planData.sessionDuration} min
                                    </p>
                                    <p className="text-xs text-slate-600 mt-1">
                                        {planData.availableDays.slice(0, 3).map(d => d.slice(0, 3)).join(", ")}
                                        {planData.availableDays.length > 3 && ` +${planData.availableDays.length - 3}`}
                                    </p>
                                </div>
                            )}
                            {planData.equipment.length > 0 && (
                                <div>
                                    <p className="text-sm text-slate-600 mb-1">Equipment</p>
                                    <p className="text-sm font-medium text-slate-900">
                                        {planData.equipment.length} selected
                                    </p>
                                </div>
                            )}
                            {planData.workoutType.length > 0 && (
                                <div>
                                    <p className="text-sm text-slate-600 mb-1">Workout Types</p>
                                    <div className="flex flex-wrap gap-1">
                                        {planData.workoutType.map(type => (
                                            <Badge key={type} variant="outline" className="text-xs">
                                                {WORKOUT_TYPES.find(t => t.value === type)?.label}
                                            </Badge>
                                        ))}
                                    </div>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    <Card className="bg-gradient-to-br from-purple-50 to-blue-50 border-purple-200">
                        <CardContent className="p-4 space-y-2">
                            <div className="flex items-center gap-2">
                                <Brain className="w-5 h-5 text-purple-600" />
                                <p className="font-semibold text-slate-900">AI-Powered</p>
                            </div>
                            <p className="text-sm text-slate-700">
                                Our AI analyzes your inputs to create a scientifically-backed, personalized workout plan optimized for your specific goals and constraints.
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </div>

            {/* View All Goals Modal */}
            {showAllGoals && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div className="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[80vh] flex flex-col mx-4">
                        <div className="flex items-center justify-between p-6 border-b border-slate-200">
                            <div>
                                <h2 className="text-xl font-bold text-slate-900">All Fitness Goals</h2>
                                <p className="text-sm text-slate-500 mt-0.5">Select up to 3 goals</p>
                            </div>
                            <div className="flex items-center gap-3">
                                {planData.fitnessGoals.length > 0 && (
                                    <span className="text-sm text-blue-600 font-medium">{planData.fitnessGoals.length}/3 selected</span>
                                )}
                                <button
                                    onClick={() => setShowAllGoals(false)}
                                    className="p-2 rounded-lg hover:bg-slate-100 transition-colors"
                                >
                                    <X className="w-5 h-5 text-slate-600" />
                                </button>
                            </div>
                        </div>
                        <div className="overflow-y-auto flex-1 p-6">
                            <div className="grid md:grid-cols-2 gap-3">
                                {FITNESS_GOALS.map((goal) => {
                                    const isSelected = planData.fitnessGoals.includes(goal.value);
                                    return (
                                        <button
                                            key={goal.value}
                                            onClick={() => handleGoalToggle(goal.value)}
                                            className={`p-4 border-2 rounded-xl transition-all text-left ${isSelected
                                                ? "border-blue-600 bg-blue-50"
                                                : planData.fitnessGoals.length >= 3
                                                    ? "border-slate-200 opacity-50 cursor-not-allowed"
                                                    : "border-slate-200 hover:border-blue-600 hover:bg-blue-50"
                                                }`}
                                        >
                                            <div className="flex items-start gap-3">
                                                <span className="text-2xl">{goal.icon}</span>
                                                <div className="flex-1">
                                                    <p className={`font-semibold ${isSelected ? "text-blue-600" : "text-slate-900"}`}>
                                                        {goal.label}
                                                    </p>
                                                    <p className="text-sm text-slate-600">{goal.description}</p>
                                                </div>
                                                {isSelected && <Check className="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />}
                                            </div>
                                        </button>
                                    );
                                })}
                            </div>
                        </div>
                        <div className="p-6 border-t border-slate-200">
                            <Button
                                onClick={() => setShowAllGoals(false)}
                                className="w-full bg-gradient-to-r from-blue-600 to-indigo-600"
                                disabled={planData.fitnessGoals.length === 0}
                            >
                                Confirm {planData.fitnessGoals.length > 0 ? `(${planData.fitnessGoals.length} selected)` : ""} <Check className="w-4 h-4 ml-2" />
                            </Button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
