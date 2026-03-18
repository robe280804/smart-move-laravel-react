import { STEPS } from "@/constants/welcome";
import { AnimatedSection } from "./AnimatedSection";

export function HowItWorks() {
    return (
        <section id="how-it-works" className="py-24 bg-slate-50">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                {/* Section header */}
                <AnimatedSection className="text-center mb-16 space-y-4">
                    <span className="inline-block px-4 py-1.5 rounded-full bg-indigo-50 text-indigo-600 text-sm font-semibold border border-indigo-100">
                        Simple Process
                    </span>
                    <h2 className="text-4xl md:text-5xl font-bold text-slate-900">
                        How It Works
                    </h2>
                    <p className="text-xl text-slate-500 max-w-2xl mx-auto">
                        From zero to your first personalized workout in under 5 minutes
                    </p>
                </AnimatedSection>

                <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-6 relative">
                    {/* Connecting line (desktop only) */}
                    <div className="hidden lg:block absolute top-10 left-[12.5%] right-[12.5%] h-0.5 bg-gradient-to-r from-blue-200 via-indigo-300 to-blue-200 z-0" />

                    {STEPS.map((step, index) => {
                        const Icon = step.icon;
                        return (
                            <AnimatedSection key={index} delay={index * 100}>
                                <div className="group relative z-10 flex flex-col items-center text-center p-6 rounded-2xl hover:bg-white hover:shadow-lg transition-all duration-300 cursor-default h-full">
                                    {/* Step number bubble */}
                                    <div className="w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-200 mb-5 group-hover:scale-110 group-hover:shadow-indigo-300 group-hover:shadow-xl transition-all duration-300">
                                        <span className="text-white text-xl font-bold">{step.number}</span>
                                    </div>

                                    <div className="flex items-center gap-2 mb-2">
                                        <Icon className="w-4 h-4 text-indigo-400 group-hover:text-indigo-600 transition-colors duration-200" />
                                        <h3 className="text-lg font-semibold text-slate-900 group-hover:text-indigo-700 transition-colors duration-200">
                                            {step.title}
                                        </h3>
                                    </div>
                                    <p className="text-sm text-slate-500 leading-relaxed max-w-[200px]">
                                        {step.description}
                                    </p>
                                </div>
                            </AnimatedSection>
                        );
                    })}
                </div>
            </div>
        </section>
    );
}
