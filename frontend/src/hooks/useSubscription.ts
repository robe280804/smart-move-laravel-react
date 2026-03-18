import { useState, useEffect } from "react";
import { getSubscriptionPlan, getBillingPortalUrl, redirectToStripeCheckout } from "@/services/payment";
import type { PlanKey } from "@/constants/welcome";
import { toast } from "sonner";

export function useSubscription() {
    const [currentPlan, setCurrentPlan] = useState<PlanKey | null>(null);
    const [isPlanLoading, setIsPlanLoading] = useState(false);
    const [checkoutLoadingPlan, setCheckoutLoadingPlan] = useState<PlanKey | null>(null);

    useEffect(() => {
        getSubscriptionPlan()
            .then(setCurrentPlan)
            .catch(() => setCurrentPlan("free"));
    }, []);

    const handleSelectPlan = async (planKey: Exclude<PlanKey, "free">) => {
        setCheckoutLoadingPlan(planKey);
        try {
            const result = await redirectToStripeCheckout(planKey);
            if (result.swapped) {
                setCurrentPlan(planKey);
                toast.success("Plan updated successfully.", {
                    position: "top-center",
                    duration: 5000,
                    style: { background: "#22C55E", color: "#fff" },
                });
            }
        } catch (error) {
            toast.error(error instanceof Error ? error.message : "Something went wrong. Please try again.", {
                position: "top-center",
                duration: 5000,
                style: { background: "#FF4D4F", color: "#fff" },
            });
        } finally {
            setCheckoutLoadingPlan(null);
        }
    };

    const handleManageBilling = async () => {
        setIsPlanLoading(true);
        try {
            const url = await getBillingPortalUrl();
            window.location.href = url;
        } catch (error) {
            toast.error(error instanceof Error ? error.message : "Unable to open billing portal.", {
                position: "top-center",
                duration: 5000,
                style: { background: "#FF4D4F", color: "#fff" },
            });
        } finally {
            setIsPlanLoading(false);
        }
    };

    return { currentPlan, isPlanLoading, checkoutLoadingPlan, handleSelectPlan, handleManageBilling };
}
