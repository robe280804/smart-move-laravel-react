import { Card, CardContent } from "@/components/ui/card";
import { FEATURES } from "@/constants/welcome";
import { AnimatedSection } from "./AnimatedSection";

export function FeaturesSection() {
    return (
        <section id="features" className="py-24 bg-white">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                {/* Section header */}
                <AnimatedSection className="text-center mb-16 space-y-4">
                    <span className="inline-block px-4 py-1.5 rounded-full bg-blue-50 text-blue-600 text-sm font-semibold border border-blue-100">
                        Why Smart Move AI
                    </span>
                    <h2 className="text-4xl md:text-5xl font-bold text-slate-900">
                        Everything You Need to Succeed
                    </h2>
                    <p className="text-xl text-slate-500 max-w-2xl mx-auto">
                        Cutting-edge technology meets fitness expertise to deliver unmatched personalization
                    </p>
                </AnimatedSection>

                {/* Cards grid — each card staggers its entrance */}
                <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {FEATURES.map((feature, index) => {
                        const Icon = feature.icon;
                        return (
                            <AnimatedSection key={index} delay={index * 80}>
                                <Card className="group h-full border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 cursor-default overflow-hidden">
                                    {/* Top accent bar slides in on hover */}
                                    <div className={`h-1 w-0 group-hover:w-full bg-gradient-to-r ${feature.gradient} transition-all duration-300`} />
                                    <CardContent className="p-6 space-y-4">
                                        <div className={`w-12 h-12 rounded-xl bg-gradient-to-br ${feature.gradient} flex items-center justify-center shadow-md group-hover:scale-110 group-hover:shadow-lg transition-all duration-300`}>
                                            <Icon className="w-6 h-6 text-white" />
                                        </div>
                                        <div className="space-y-1.5">
                                            <h3 className="text-lg font-semibold text-slate-900 group-hover:text-indigo-700 transition-colors duration-200">
                                                {feature.title}
                                            </h3>
                                            <p className="text-slate-500 text-sm leading-relaxed">
                                                {feature.description}
                                            </p>
                                        </div>
                                    </CardContent>
                                </Card>
                            </AnimatedSection>
                        );
                    })}
                </div>
            </div>
        </section>
    );
}
