import type { PlanKey } from "@/constants/welcome";

// Payment Links created in the Stripe dashboard (Stripe test mode).
// Go to: Stripe Dashboard → Payment Links → Create link → paste the URL below.
// No backend or secret key needed for these to work.
const PAYMENT_LINKS: Record<Exclude<PlanKey, "free">, string> = {
    advanced: import.meta.env.VITE_STRIPE_PAYMENT_LINK_ADVANCED ?? "",
    pro: import.meta.env.VITE_STRIPE_PAYMENT_LINK_PRO ?? "",
};

/**
 * Redirects the user to a Stripe-hosted Payment Link.
 * Frontend-only — no backend required.
 *
 * When the backend is ready, replace this with a call to POST /payments/checkout
 * which returns a Checkout Session URL, then redirect to that URL instead.
 */
export function redirectToStripeCheckout(planKey: Exclude<PlanKey, "free">): void {
    const url = PAYMENT_LINKS[planKey];

    if (!url) {
        throw new Error(
            `No payment link configured for plan "${planKey}". ` +
            `Set VITE_STRIPE_PAYMENT_LINK_${planKey.toUpperCase()} in your .env file.`,
        );
    }

    window.location.href = url;
}
