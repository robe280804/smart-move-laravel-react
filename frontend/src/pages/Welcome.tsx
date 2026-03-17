import { useNavigate } from "react-router";
import { useEffect, useState } from "react";
import { WelcomeHero } from "../components/welcome/WelcomeHero";
import { FeaturesSection } from "../components/welcome/FeatureSection";
import { HowItWorks } from "../components/welcome/HowItWorks";
import { PricingSection } from "../components/welcome/PricingSection";
import { CTASection } from "../components/welcome/CTASection";
import { Footer } from "../components/welcome/Footer";
import { useAuth } from "../contexts/AuthContext";

export function WelcomePage() {
    const { isAuthenticated, isLoading } = useAuth();
    const navigate = useNavigate();
    const [pendingNavigation, setPendingNavigation] = useState(false);

    // Resolve the navigation once the auth check completes
    useEffect(() => {
        if (pendingNavigation && !isLoading) {
            navigate(isAuthenticated ? "/dashboard" : "/register");
        }
    }, [isLoading, pendingNavigation, isAuthenticated, navigate]);

    const handleGetStarted = () => {
        if (isLoading) {
            // Auth check still in flight — defer navigation until it resolves
            setPendingNavigation(true);
            return;
        }
        navigate(isAuthenticated ? "/dashboard" : "/register");
    };

    return (
        <div className="size-full">
            <WelcomeHero onGetStarted={handleGetStarted} />
            <FeaturesSection />
            <HowItWorks />
            <PricingSection onGetStarted={handleGetStarted} />
            <CTASection onGetStarted={handleGetStarted} />
            <Footer />
        </div>
    );
}
