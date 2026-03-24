import { useState, useEffect } from "react";
import { getSubscriptionPlan, getBillingPortalUrl, redirectToStripeCheckout } from "@/services/payment";
import type { PlanKey } from "@/constants/welcome";
import { notify } from "@/lib/toast";

const PLAN_CACHE_KEY = "sm_subscription_plan";

function getCachedPlan(): PlanKey | null {
    try {
        return sessionStorage.getItem(PLAN_CACHE_KEY) as PlanKey | null;
    } catch {
        return null;
    }
}

function setCachedPlan(plan: PlanKey) {
    try {
        sessionStorage.setItem(PLAN_CACHE_KEY, plan);
    } catch {
        // ignore
    }
}

function clearCachedPlan() {
    try {
        sessionStorage.removeItem(PLAN_CACHE_KEY);
    } catch {
        // ignore
    }
}

export function useSubscription() {
    const [currentPlan, setCurrentPlan] = useState<PlanKey | null>(getCachedPlan);
    const [isPlanLoading, setIsPlanLoading] = useState(false);
    const [checkoutLoadingPlan, setCheckoutLoadingPlan] = useState<PlanKey | null>(null);

    const fetchPlan = (force = false) => {
        if (!force && getCachedPlan()) return;
        getSubscriptionPlan()
            .then((plan) => {
                setCurrentPlan(plan);
                setCachedPlan(plan);
            })
            .catch(() => setCurrentPlan("free"));
    };

    useEffect(() => {
        fetchPlan();
    }, []);

    const handleSelectPlan = async (planKey: Exclude<PlanKey, "free">) => {
        setCheckoutLoadingPlan(planKey);
        clearCachedPlan();
        try {
            const result = await redirectToStripeCheckout(planKey);
            if (result.swapped) {
                setCurrentPlan(planKey);
                setCachedPlan(planKey);
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
        clearCachedPlan();
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

    const refetchPlan = () => {
        clearCachedPlan();
        fetchPlan(true);
    };

    return { currentPlan, isPlanLoading, checkoutLoadingPlan, handleSelectPlan, handleManageBilling, canExportPdf, canEditExercises, refetchPlan };
}
