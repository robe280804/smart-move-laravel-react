import { useState, useEffect } from "react";
import { getSubscriptionPlan, getBillingPortalUrl, redirectToStripeCheckout } from "@/services/payment";
import type { PlanKey } from "@/constants/welcome";
import { notify } from "@/lib/toast";

export function useSubscription() {
    const [currentPlan, setCurrentPlan] = useState<PlanKey | null>(null);
    const [isPlanLoading, setIsPlanLoading] = useState(false);
    const [checkoutLoadingPlan, setCheckoutLoadingPlan] = useState<PlanKey | null>(null);

    const fetchPlan = () => {
        getSubscriptionPlan()
            .then(setCurrentPlan)
            .catch(() => setCurrentPlan("free"));
    };

    useEffect(() => {
        fetchPlan();
    }, []);

    const handleSelectPlan = async (planKey: Exclude<PlanKey, "free">) => {
        setCheckoutLoadingPlan(planKey);
        try {
            const result = await redirectToStripeCheckout(planKey);
            if (result.swapped) {
                setCurrentPlan(planKey);
                notify.success("Plan updated successfully.");
            }
        } catch (error) {
            notify.error(error instanceof Error ? error.message : "Something went wrong. Please try again.");
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
            notify.error(error instanceof Error ? error.message : "Unable to open billing portal.");
        } finally {
            setIsPlanLoading(false);
        }
    };

    const canExportPdf = currentPlan !== null && currentPlan !== "free";
    const canEditExercises = currentPlan !== null && currentPlan !== "free";

    return { currentPlan, isPlanLoading, checkoutLoadingPlan, handleSelectPlan, handleManageBilling, canExportPdf, canEditExercises, refetchPlan: fetchPlan };
}
