import { Button } from "@/components/ui/button";
import { ArrowRight, Sparkles } from "lucide-react";
import { ImageWithFallback } from "./ImageWithFallaback";
import { HERO_STATS } from "@/constants/welcome";
import { AnimatedSection } from "./AnimatedSection";

export function WelcomeHero({ onGetStarted }: { onGetStarted: () => void }) {
    return (
        <section
            id="hero"
            className="relative min-h-screen flex items-center justify-center overflow-hidden bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 pt-16"
        >
            {/* Background grid pattern */}
            <div className="absolute inset-0 bg-grid-slate-900/[0.04] bg-[size:32px_32px]" />

            {/* Ambient orbs */}
            <div className="absolute top-20 left-10 w-72 h-72 bg-blue-400/20 rounded-full blur-3xl animate-pulse" />
            <div className="absolute bottom-20 right-10 w-96 h-96 bg-indigo-400/20 rounded-full blur-3xl animate-pulse delay-1000" />

            <div className="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
                <div className="grid lg:grid-cols-2 gap-12 items-center">

                    {/* Left — text content, fades in from the left */}
                    <AnimatedSection direction="left" className="space-y-8">
                        {/* Badge */}
                        <div className="inline-flex items-center gap-2 px-4 py-2 bg-white/80 backdrop-blur-sm rounded-full border border-blue-200 shadow-sm hover:border-blue-400 hover:shadow-md hover:bg-white transition-all duration-200 cursor-default">
                            <Sparkles className="w-4 h-4 text-blue-600" />
                            <span className="text-sm text-slate-700">AI-Powered Fitness Planning</span>
                        </div>

                        <div className="space-y-4">
                            <h1 className="text-5xl md:text-6xl lg:text-7xl font-bold tracking-tight text-slate-900">
                                Welcome to{" "}
                                <span className="bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                                    Smart Move AI
                                </span>
                            </h1>
                            <p className="text-xl md:text-2xl text-slate-600 max-w-xl leading-relaxed">
                                Your personalized fitness journey starts here. Get AI-customized workout plans tailored to your goals, fitness level, and lifestyle.
                            </p>
                        </div>

                        <div className="space-y-3">
                            <Button
                                onClick={onGetStarted}
                                size="lg"
                                className="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-8 py-6 text-lg group shadow-lg hover:shadow-xl hover:shadow-indigo-200 transition-all duration-200"
                            >
                                Create Your Plan
                                <ArrowRight className="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" />
                            </Button>
                            <p className="text-sm text-slate-500">No credit card required · Get started in 2 minutes</p>
                        </div>

                        {/* Stats row */}
                        <div className="grid grid-cols-3 gap-4 pt-8 border-t border-slate-200">
                            {HERO_STATS.map((stat) => (
                                <div
                                    key={stat.label}
                                    className="group p-3 rounded-xl hover:bg-white hover:shadow-md transition-all duration-200 cursor-default"
                                >
                                    <div className="text-3xl font-bold text-slate-900 group-hover:text-indigo-600 transition-colors duration-200">
                                        {stat.value}
                                    </div>
                                    <div className="text-sm text-slate-500">{stat.label}</div>
                                </div>
                            ))}
                        </div>
                    </AnimatedSection>

                    {/* Right — image, fades in from the right with a slight delay */}
                    <AnimatedSection direction="right" delay={150} className="relative">
                        <div className="absolute -inset-4 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-3xl blur-2xl opacity-20" />
                        <div className="relative group">
                            <ImageWithFallback
                                src="/hero-fitness.jpg"
                                alt="Fitness training"
                                className="rounded-2xl shadow-2xl w-full h-[600px] object-cover group-hover:scale-[1.01] transition-transform duration-500"
                            />
                            {/* Floating overlay card */}
                            <div className="absolute bottom-6 left-6 bg-white/95 backdrop-blur-sm rounded-xl p-4 shadow-lg border border-white/20 hover:-translate-y-1 hover:shadow-xl transition-all duration-200">
                                <div className="flex items-center gap-3">
                                    <div className="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                                        <Sparkles className="w-6 h-6 text-white animate-pulse" />
                                    </div>
                                    <div>
                                        <div className="text-sm font-semibold text-slate-900">AI Analyzing...</div>
                                        <div className="text-xs text-slate-500">Customizing your plan</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </AnimatedSection>
                </div>
            </div>

            {/* Scroll indicator */}
            <div className="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-1 animate-bounce opacity-60">
                <div className="w-px h-6 bg-slate-400 rounded-full" />
                <div className="w-1.5 h-1.5 rounded-full bg-slate-400" />
            </div>
        </section>
    );
}
