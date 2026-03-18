import { api } from "@/lib/api";
import { handleApiError } from "@/lib/handleApiError";
import type { PlanKey } from "@/constants/welcome";

export type CheckoutResult = { swapped: true } | { swapped: false; checkoutUrl: string };

export const getSubscriptionPlan = async (): Promise<PlanKey> => {
    try {
        const response = await api.get<{ data: { plan: PlanKey } }>("/payments/plan");
        return response.data.data.plan;
    } catch (error) {
        return handleApiError(error);
    }
};

export const getBillingPortalUrl = async (): Promise<string> => {
    try {
        const response = await api.post<{ data: { url: string } }>("/payments/billing-portal");
        return response.data.data.url;
    } catch (error) {
        return handleApiError(error);
    }
};

/**
 * Initiates a checkout for a paid plan.
 * Returns { swapped: true } when the plan was upgraded/downgraded inline (no redirect needed).
 * Returns { swapped: false, checkoutUrl } when a Stripe Checkout Session was created.
 */
export const redirectToStripeCheckout = async (planKey: Exclude<PlanKey, "free">): Promise<CheckoutResult> => {
    try {
        const response = await api.post<{ data: { checkout_url: string | null } }>("/payments/checkout", {
            plan: planKey,
        });

        const checkoutUrl = response.data.data.checkout_url;

        if (checkoutUrl === null) {
            return { swapped: true };
        }

        window.location.href = checkoutUrl;
        return { swapped: false, checkoutUrl };
    } catch (error) {
        return handleApiError(error);
    }
};
