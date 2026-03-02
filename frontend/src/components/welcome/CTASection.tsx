import { Button } from "../ui/button";
import { ArrowRight, CheckCircle } from "lucide-react";

const benefits = [
    "Personalized AI-generated workout plans",
    "Progress tracking and analytics",
    "Exercise library with video guides",
    "Adaptive difficulty adjustments",
    "Nutritional recommendations"
];

export function CTASection({ onGetStarted }: { onGetStarted: () => void }) {
    return (
        <div className="py-24 bg-gradient-to-br from-blue-600 to-indigo-700 relative overflow-hidden">
            {/* Background Pattern */}
            <div className="absolute inset-0 bg-grid-white/[0.05] bg-[size:32px_32px]" />

            <div className="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div className="space-y-8">
                    <h2 className="text-4xl md:text-5xl text-white">
                        Ready to Transform Your Fitness?
                    </h2>
                    <p className="text-xl text-blue-100 max-w-2xl mx-auto">
                        Join thousands of users who have already achieved their fitness goals with Smart Move AI
                    </p>

                    <div className="inline-flex flex-col items-center gap-6 bg-white/10 backdrop-blur-sm rounded-2xl p-8 border border-white/20">
                        <div className="space-y-3">
                            {benefits.map((benefit, index) => (
                                <div key={index} className="flex items-center gap-3 text-white">
                                    <CheckCircle className="w-5 h-5 text-green-300 flex-shrink-0" />
                                    <span className="text-lg">{benefit}</span>
                                </div>
                            ))}
                        </div>

                        <Button
                            onClick={onGetStarted}
                            size="lg"
                            className="bg-white text-blue-600 hover:bg-blue-50 px-8 py-6 text-lg group shadow-xl"
                        >
                            Get Started Free
                            <ArrowRight className="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" />
                        </Button>
                        <p className="text-sm text-blue-200">Start creating your custom plan today</p>
                    </div>
                </div>
            </div>
        </div>
    );
}
