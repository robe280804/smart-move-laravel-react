import { ClipboardList, Cpu, Dumbbell, Trophy } from "lucide-react";
import { ImageWithFallback } from "./ImageWithFallaback";

const steps = [
    {
        number: "01",
        icon: ClipboardList,
        title: "Tell Us About You",
        description: "Share your fitness level, goals, available equipment, and schedule preferences."
    },
    {
        number: "02",
        icon: Cpu,
        title: "AI Creates Your Plan",
        description: "Our advanced AI analyzes your profile and generates a customized workout plan in seconds."
    },
    {
        number: "03",
        icon: Dumbbell,
        title: "Start Training",
        description: "Follow your personalized plan with guided exercises, videos, and progress tracking."
    },
    {
        number: "04",
        icon: Trophy,
        title: "Achieve Your Goals",
        description: "Watch as your plan adapts to your progress and celebrates your milestones."
    }
];

export function HowItWorks() {
    return (
        <div className="py-24 bg-gradient-to-br from-slate-50 to-blue-50">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="text-center mb-16 space-y-4">
                    <h2 className="text-4xl md:text-5xl text-slate-900">
                        How It Works
                    </h2>
                    <p className="text-xl text-slate-600 max-w-2xl mx-auto">
                        Get started with your personalized fitness journey in four simple steps
                    </p>
                </div>

                <div className="grid lg:grid-cols-2 gap-12 items-center mb-16">
                    {/* Steps */}
                    <div className="space-y-6">
                        {steps.map((step, index) => {
                            const Icon = step.icon;
                            return (
                                <div
                                    key={index}
                                    className="flex gap-6 p-6 bg-white rounded-2xl shadow-md hover:shadow-lg transition-shadow"
                                >
                                    <div className="flex-shrink-0">
                                        <div className="w-16 h-16 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white text-xl font-bold">
                                            {step.number}
                                        </div>
                                    </div>
                                    <div className="space-y-2 flex-1">
                                        <div className="flex items-center gap-3">
                                            <Icon className="w-5 h-5 text-blue-600" />
                                            <h3 className="text-xl font-semibold text-slate-900">
                                                {step.title}
                                            </h3>
                                        </div>
                                        <p className="text-slate-600">
                                            {step.description}
                                        </p>
                                    </div>
                                </div>
                            );
                        })}
                    </div>

                    {/* Image */}
                    <div className="relative">
                        <div className="absolute -inset-4 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-3xl blur-2xl opacity-20" />
                        <div className="relative">
                            <ImageWithFallback
                                src="https://images.unsplash.com/photo-1564282350350-a8355817fd2e?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxwZXJzb25hbCUyMHRyYWluZXIlMjBhdGhsZXRlfGVufDF8fHx8MTc3MTk0NjA4Mnww&ixlib=rb-4.1.0&q=80&w=1080&utm_source=figma&utm_medium=referral"
                                alt="Personal trainer athlete"
                                className="rounded-2xl shadow-2xl w-full h-[550px] object-cover"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
