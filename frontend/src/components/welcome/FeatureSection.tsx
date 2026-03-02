import { Brain, Target, Zap, Calendar, TrendingUp, Users } from "lucide-react";
import { Card, CardContent } from "../ui/card";

const features = [
    {
        icon: Brain,
        title: "AI-Powered Intelligence",
        description: "Advanced algorithms analyze your fitness profile to create perfectly tailored workout plans.",
        gradient: "from-blue-500 to-cyan-500"
    },
    {
        icon: Target,
        title: "Goal-Oriented Plans",
        description: "Whether it's weight loss, muscle gain, or endurance, we customize plans for your specific goals.",
        gradient: "from-indigo-500 to-purple-500"
    },
    {
        icon: Zap,
        title: "Adaptive Workouts",
        description: "Plans that evolve with your progress, automatically adjusting intensity and exercises.",
        gradient: "from-purple-500 to-pink-500"
    },
    {
        icon: Calendar,
        title: "Flexible Scheduling",
        description: "Fit workouts into your busy life with plans that adapt to your available time.",
        gradient: "from-orange-500 to-red-500"
    },
    {
        icon: TrendingUp,
        title: "Progress Tracking",
        description: "Visualize your improvements with detailed analytics and milestone celebrations.",
        gradient: "from-green-500 to-emerald-500"
    },
    {
        icon: Users,
        title: "Expert Backed",
        description: "Developed with certified trainers and sports scientists to ensure safe, effective results.",
        gradient: "from-teal-500 to-cyan-500"
    }
];

export function FeaturesSection() {
    return (
        <div className="py-24 bg-white">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="text-center mb-16 space-y-4">
                    <h2 className="text-4xl md:text-5xl text-slate-900">
                        Why Choose Smart Move AI?
                    </h2>
                    <p className="text-xl text-slate-600 max-w-2xl mx-auto">
                        Cutting-edge technology meets fitness expertise to deliver unmatched personalization
                    </p>
                </div>

                <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    {features.map((feature, index) => {
                        const Icon = feature.icon;
                        return (
                            <Card
                                key={index}
                                className="border-0 shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 bg-gradient-to-br from-white to-slate-50"
                            >
                                <CardContent className="p-6 space-y-4">
                                    <div className={`w-14 h-14 rounded-xl bg-gradient-to-br ${feature.gradient} flex items-center justify-center shadow-lg`}>
                                        <Icon className="w-7 h-7 text-white" />
                                    </div>
                                    <h3 className="text-xl font-semibold text-slate-900">
                                        {feature.title}
                                    </h3>
                                    <p className="text-slate-600 leading-relaxed">
                                        {feature.description}
                                    </p>
                                </CardContent>
                            </Card>
                        );
                    })}
                </div>
            </div>
        </div>
    );
}
