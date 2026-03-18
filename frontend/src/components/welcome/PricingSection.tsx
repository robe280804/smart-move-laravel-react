import { Button } from "@/components/ui/button";
import { Check, X, PRICING_TIERS } from "@/constants/welcome";
import type { PlanKey } from "@/constants/welcome";
import { AnimatedSection } from "./AnimatedSection";

interface PricingSectionProps {
    onGetStarted: () => void;
    onSelectPlan: (planKey: Exclude<PlanKey, "free">) => void;
}

export function PricingSection({ onGetStarted, onSelectPlan }: PricingSectionProps) {
    return (
        <section id="pricing" className="py-24 bg-white">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                {/* Section header */}
                <AnimatedSection className="text-center mb-16 space-y-4">
                    <span className="inline-block px-4 py-1.5 rounded-full bg-blue-50 text-blue-600 text-sm font-semibold border border-blue-100">
                        Pricing
                    </span>
                    <h2 className="text-4xl md:text-5xl font-bold text-slate-900">
                        Simple, Transparent Pricing
                    </h2>
                    <p className="text-xl text-slate-500 max-w-2xl mx-auto">
                        Start for free and upgrade when you're ready to go further
                    </p>
                </AnimatedSection>

                <div className="grid md:grid-cols-3 gap-8 items-stretch">
                    {PRICING_TIERS.map((tier, index) => {
                        const isLight = !tier.highlighted && tier.name === "Free";
                        const textMuted = tier.highlighted ? "text-blue-200" : "text-slate-400";
                        const textBase = tier.highlighted || tier.name === "Pro" ? "text-white" : "text-slate-700";
                        const divider = tier.highlighted ? "border-blue-500" : tier.name === "Pro" ? "border-slate-700" : "border-slate-100";

                        return (
                            <AnimatedSection key={tier.name} delay={index * 100}>
                                <div
                                    className={`relative rounded-2xl p-8 flex flex-col shadow-lg h-full ${tier.cardStyle} ${
                                        tier.highlighted ? "scale-105 shadow-2xl shadow-indigo-200 z-10" : "hover:-translate-y-1 hover:shadow-xl transition-all duration-300"
                                    }`}
                                >
                                    {/* Popular badge */}
                                    {tier.badge && (
                                        <div className="absolute -top-4 left-1/2 -translate-x-1/2">
                                            <span className="bg-gradient-to-r from-orange-400 to-pink-500 text-white text-xs font-bold px-4 py-1.5 rounded-full shadow-md">
                                                {tier.badge}
                                            </span>
                                        </div>
                                    )}

                                    {/* Header */}
                                    <div className="mb-6">
                                        <h3 className={`text-lg font-bold mb-1 ${textBase}`}>{tier.name}</h3>
                                        <p className={`text-sm leading-relaxed ${textMuted}`}>{tier.description}</p>
                                    </div>

                                    {/* Price */}
                                    <div className={`flex items-end gap-1 mb-6 pb-6 border-b ${divider}`}>
                                        <span className={`text-5xl font-extrabold ${textBase}`}>{tier.price}</span>
                                        <span className={`text-sm mb-2 ${textMuted}`}>/ {tier.period}</span>
                                    </div>

                                    {/* Feature list */}
                                    <ul className="space-y-3 flex-1 mb-8">
                                        {tier.features.map((feature, i) => (
                                            <li key={i} className="flex items-start gap-3">
                                                <div className="flex-shrink-0 mt-0.5">
                                                    {feature.included ? (
                                                        <div className={`w-5 h-5 rounded-full flex items-center justify-center ${tier.highlighted ? "bg-white/20" : "bg-green-50"}`}>
                                                            <Check className={`w-3 h-3 ${tier.highlighted ? "text-white" : "text-green-600"}`} />
                                                        </div>
                                                    ) : (
                                                        <div className={`w-5 h-5 rounded-full flex items-center justify-center ${tier.highlighted ? "bg-white/10" : isLight ? "bg-slate-50" : "bg-slate-800"}`}>
                                                            <X className={`w-3 h-3 ${tier.highlighted ? "text-blue-200" : isLight ? "text-slate-300" : "text-slate-600"}`} />
                                                        </div>
                                                    )}
                                                </div>
                                                <span className={`text-sm ${feature.included ? textBase : textMuted}`}>
                                                    {feature.label}
                                                    {feature.highlight && (
                                                        <span className={`ml-1.5 font-semibold ${tier.highlighted ? "text-white" : tier.name === "Pro" ? "text-indigo-400" : "text-indigo-600"}`}>
                                                            — {feature.highlight}
                                                        </span>
                                                    )}
                                                </span>
                                            </li>
                                        ))}
                                    </ul>

                                    {/* CTA */}
                                    <Button
                                        onClick={() =>
                                            tier.planKey === "free"
                                                ? onGetStarted()
                                                : onSelectPlan(tier.planKey)
                                        }
                                        className={`w-full py-5 font-semibold transition-all ${tier.ctaStyle}`}
                                        variant="outline"
                                    >
                                        {tier.ctaLabel}
                                    </Button>
                                </div>
                            </AnimatedSection>
                        );
                    })}
                </div>
            </div>
        </section>
    );
}
