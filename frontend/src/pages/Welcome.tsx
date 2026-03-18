import { useNavigate } from "react-router";
import { useEffect, useState } from "react";
import { toast } from "sonner";
import { WelcomeHero } from "../components/welcome/WelcomeHero";
import { FeaturesSection } from "../components/welcome/FeatureSection";
import { HowItWorks } from "../components/welcome/HowItWorks";
import { PricingSection } from "../components/welcome/PricingSection";
import { CTASection } from "../components/welcome/CTASection";
import { Footer } from "../components/welcome/Footer";
import { useAuth } from "../contexts/AuthContext";
import { redirectToStripeCheckout } from "../services/payment";
import type { PlanKey } from "../constants/welcome";

export function WelcomePage() {
    const { isAuthenticated, isLoading } = useAuth();
    const navigate = useNavigate();
    const [pendingNavigation, setPendingNavigation] = useState(false);
    const [pendingPlan, setPendingPlan] = useState<Exclude<PlanKey, "free"> | null>(null);

    // Resolve the navigation once the auth check completes
    useEffect(() => {
        if (pendingNavigation && !isLoading) {
            navigate(isAuthenticated ? "/dashboard" : "/register");
        }
    }, [isLoading, pendingNavigation, isAuthenticated, navigate]);

    // Resolve the pending plan checkout once the auth check completes
    useEffect(() => {
        if (pendingPlan && !isLoading) {
            if (!isAuthenticated) {
                navigate("/register");
            } else {
                void startCheckout(pendingPlan);
            }
            setPendingPlan(null);
        }
    }, [isLoading, pendingPlan, isAuthenticated, navigate]);

    const startCheckout = async (planKey: Exclude<PlanKey, "free">) => {
        try {
            const result = await redirectToStripeCheckout(planKey);
            if (result.swapped) {
                navigate("/dashboard");
            }
        } catch (error) {
            toast.error(error instanceof Error ? error.message : "Payment failed. Please try again.", {
                position: "top-center",
                duration: 5000,
                style: { background: "#FF4D4F", color: "#fff" },
            });
        }
    };

    const handleGetStarted = () => {
        if (isLoading) {
            // Auth check still in flight — defer navigation until it resolves
            setPendingNavigation(true);
            return;
        }
        navigate(isAuthenticated ? "/dashboard" : "/register");
    };

    const handleSelectPlan = (planKey: Exclude<PlanKey, "free">) => {
        if (isLoading) {
            // Auth check still in flight — defer until it resolves
            setPendingPlan(planKey);
            return;
        }
        if (!isAuthenticated) {
            navigate("/register");
            return;
        }
        void startCheckout(planKey);
    };

    return (
        <div className="size-full">
            <WelcomeHero onGetStarted={handleGetStarted} />
            <FeaturesSection />
            <HowItWorks />
            <PricingSection onGetStarted={handleGetStarted} onSelectPlan={handleSelectPlan} />
            <CTASection onGetStarted={handleGetStarted} />
            <Footer />
        </div>
    );
}
