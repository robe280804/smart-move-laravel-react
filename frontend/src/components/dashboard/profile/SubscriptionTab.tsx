import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Check, ExternalLink, X } from "lucide-react";
import { PRICING_TIERS } from "@/constants/welcome";
import type { PlanKey } from "@/constants/welcome";

interface SubscriptionTabProps {
    currentPlan: PlanKey | null;
    isPlanLoading: boolean;
    checkoutLoadingPlan: PlanKey | null;
    onSelectPlan: (planKey: Exclude<PlanKey, "free">) => void;
    onManageBilling: () => void;
}

const PLAN_BADGE_CLASS: Record<PlanKey, string> = {
    pro: "bg-gradient-to-r from-blue-600 to-indigo-600 text-white border-0",
    advanced: "bg-indigo-100 text-indigo-700 border-indigo-200",
    free: "bg-slate-100 text-slate-600 border-slate-200",
};

export function SubscriptionTab({ currentPlan, isPlanLoading, checkoutLoadingPlan, onSelectPlan, onManageBilling }: SubscriptionTabProps) {
    const currentTierName = PRICING_TIERS.find((t) => t.planKey === currentPlan)?.name ?? "Free";

    return (
        <>
            <Card>
                <CardHeader>
                    <div className="flex items-start justify-between gap-3">
                        <div>
                            <CardTitle>Current Plan</CardTitle>
                            <CardDescription>Your active subscription</CardDescription>
                        </div>
                        {currentPlan && (
                            <Badge className={`flex-shrink-0 ${PLAN_BADGE_CLASS[currentPlan]}`}>
                                {currentTierName}
                            </Badge>
                        )}
                    </div>
                </CardHeader>
                {currentPlan && currentPlan !== "free" && (
                    <CardContent>
                        <Button
                            variant="outline"
                            onClick={onManageBilling}
                            disabled={isPlanLoading}
                            className="gap-2"
                        >
                            <ExternalLink className="w-4 h-4" />
                            {isPlanLoading ? "Opening..." : "Manage Billing"}
                        </Button>
                        <p className="text-xs text-slate-500 mt-2">
                            Cancel, update your payment method, or view invoices.
                        </p>
                    </CardContent>
                )}
            </Card>

            <div className="grid sm:grid-cols-2 gap-4">
                {PRICING_TIERS.filter((tier) => tier.planKey !== "free").map((tier) => {
                    const isActive = currentPlan === tier.planKey;
                    const isProcessing = checkoutLoadingPlan === tier.planKey;

                    const buttonLabel = isActive
                        ? "Current Plan"
                        : isProcessing
                            ? "Processing..."
                            : currentPlan !== "free"
                                ? `Switch to ${tier.name}`
                                : `Upgrade to ${tier.name}`;

                    return (
                        <Card
                            key={tier.planKey}
                            className={isActive ? "border-indigo-400 ring-1 ring-indigo-300" : ""}
                        >
                            <CardHeader className="pb-3">
                                <div className="flex items-center justify-between">
                                    <CardTitle className="text-base">{tier.name}</CardTitle>
                                    {isActive && (
                                        <Badge className="bg-indigo-100 text-indigo-700 border-indigo-200 text-xs">
                                            Active
                                        </Badge>
                                    )}
                                </div>
                                <p className="text-2xl font-bold text-slate-900">
                                    {tier.price}
                                    <span className="text-sm font-normal text-slate-500 ml-1">
                                        {tier.period}
                                    </span>
                                </p>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <ul className="space-y-1.5 text-sm">
                                    {tier.features.map((feature) => (
                                        <li key={feature.label} className="flex items-center gap-2">
                                            {feature.included ? (
                                                <Check className="w-3.5 h-3.5 text-green-500 shrink-0" />
                                            ) : (
                                                <X className="w-3.5 h-3.5 text-slate-300 shrink-0" />
                                            )}
                                            <span className={feature.included ? "text-slate-700" : "text-slate-400"}>
                                                {feature.label}
                                                {feature.highlight && (
                                                    <span className="ml-1 font-medium text-indigo-600">
                                                        ({feature.highlight})
                                                    </span>
                                                )}
                                            </span>
                                        </li>
                                    ))}
                                </ul>
                                <Button
                                    className="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white"
                                    disabled={isActive || isProcessing}
                                    onClick={() => onSelectPlan(tier.planKey as Exclude<PlanKey, "free">)}
                                >
                                    {buttonLabel}
                                </Button>
                            </CardContent>
                        </Card>
                    );
                })}
            </div>
        </>
    );
}
