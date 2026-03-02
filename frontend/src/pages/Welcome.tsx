import { useNavigate } from "react-router";
import { WelcomeHero } from "../components/welcome/WelcomeHero";
import { FeaturesSection } from "../components/welcome/FeatureSection";
import { HowItWorks } from "../components/welcome/HowItWorks";
import { CTASection } from "../components/welcome/CTASection";
import { Footer } from "../components/welcome/Footer";

export function WelcomePage() {
    const navigate = useNavigate();

    const handleGetStarted = () => {
        // Navigate to registration page when user clicks get started
        navigate("/register");
    };

    return (
        <div className="size-full">
            <WelcomeHero onGetStarted={handleGetStarted} />
            <FeaturesSection />
            <HowItWorks />
            <CTASection onGetStarted={handleGetStarted} />
            <Footer />
        </div>
    );
}
