import { Button } from "@/components/ui/button";
import { ArrowRight, CheckCircle } from "lucide-react";
import { CTA_BENEFITS } from "@/constants/welcome";
import { AnimatedSection } from "./AnimatedSection";

interface CTASectionProps {
    onGetStarted: () => void;
}

export function CTASection({ onGetStarted }: CTASectionProps) {
    return (
        <section className="py-24 bg-gradient-to-br from-blue-600 to-indigo-700 relative overflow-hidden">
            {/* Background texture */}
            <div className="absolute inset-0 bg-grid-white/[0.05] bg-[size:32px_32px]" />
            <div className="absolute top-0 left-1/4 w-96 h-96 bg-white/5 rounded-full blur-3xl pointer-events-none" />
            <div className="absolute bottom-0 right-1/4 w-80 h-80 bg-indigo-900/30 rounded-full blur-3xl pointer-events-none" />

            <div className="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <AnimatedSection className="space-y-8">
                    <div className="space-y-4">
                        <h2 className="text-4xl md:text-5xl font-bold text-white">
                            Ready to Transform Your Fitness?
                        </h2>
                        <p className="text-xl text-blue-100 max-w-2xl mx-auto">
                            Join thousands of users who have already achieved their fitness goals with Smart Move AI
                        </p>
                    </div>

                    <div className="inline-flex flex-col items-center gap-6 bg-white/10 backdrop-blur-sm rounded-2xl p-8 border border-white/20 w-full max-w-md mx-auto hover:bg-white/15 transition-colors duration-300">
                        <ul className="space-y-3 w-full">
                            {CTA_BENEFITS.map((benefit, index) => (
                                <li
                                    key={index}
                                    className="flex items-center gap-3 text-white text-left group/item hover:translate-x-1 transition-transform duration-200 cursor-default"
                                >
                                    <CheckCircle className="w-5 h-5 text-green-300 flex-shrink-0 group-hover/item:scale-110 transition-transform duration-200" />
                                    <span>{benefit}</span>
                                </li>
                            ))}
                        </ul>

                        <Button
                            onClick={onGetStarted}
                            size="lg"
                            className="w-full bg-white text-blue-600 hover:bg-blue-50 hover:shadow-xl hover:-translate-y-0.5 text-lg font-semibold group shadow-lg transition-all duration-200"
                        >
                            Get Started Free
                            <ArrowRight className="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" />
                        </Button>
                        <p className="text-sm text-blue-200">No credit card required · Start in 2 minutes</p>
                    </div>
                </AnimatedSection>
            </div>
        </section>
    );
}
