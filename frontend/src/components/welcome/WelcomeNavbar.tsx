import { useState, useEffect } from "react";
import { Button } from "@/components/ui/button";
import { Dumbbell, Menu, X } from "lucide-react";

interface WelcomeNavbarProps {
    onGetStarted: () => void;
    onLogin: () => void;
}

const NAV_LINKS = [
    { label: "Features", id: "features" },
    { label: "How It Works", id: "how-it-works" },
    { label: "Pricing", id: "pricing" },
] as const;

export function WelcomeNavbar({ onGetStarted, onLogin }: WelcomeNavbarProps) {
    const [scrolled, setScrolled] = useState(false);
    const [menuOpen, setMenuOpen] = useState(false);

    useEffect(() => {
        const handleScroll = () => setScrolled(window.scrollY > 24);
        window.addEventListener("scroll", handleScroll, { passive: true });
        return () => window.removeEventListener("scroll", handleScroll);
    }, []);

    const scrollTo = (id: string) => {
        document.getElementById(id)?.scrollIntoView({ behavior: "smooth" });
        setMenuOpen(false);
    };

    return (
        <header
            className={`fixed top-0 left-0 right-0 z-50 transition-all duration-300 ${
                scrolled
                    ? "bg-white/95 backdrop-blur-md shadow-sm border-b border-slate-100"
                    : "bg-transparent"
            }`}
        >
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="flex items-center justify-between h-16">

                    {/* Logo */}
                    <div className="flex items-center gap-2 cursor-default select-none">
                        <div className="w-8 h-8 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center shadow-sm">
                            <Dumbbell className="w-4 h-4 text-white" />
                        </div>
                        <span className="font-bold text-lg text-slate-900" translate="no">
                            Smart Move AI
                        </span>
                    </div>

                    {/* Desktop nav links */}
                    <nav className="hidden md:flex items-center gap-1">
                        {NAV_LINKS.map((item) => (
                            <button
                                key={item.id}
                                onClick={() => scrollTo(item.id)}
                                className="px-4 py-2 text-sm font-medium text-slate-600 hover:text-blue-600 hover:bg-blue-50/60 rounded-lg transition-colors duration-200"
                            >
                                {item.label}
                            </button>
                        ))}
                    </nav>

                    {/* Desktop CTAs */}
                    <div className="hidden md:flex items-center gap-2">
                        <Button
                            variant="ghost"
                            onClick={onLogin}
                            className="text-slate-700 hover:text-blue-600 hover:bg-blue-50/60"
                        >
                            Log In
                        </Button>
                        <Button
                            onClick={onGetStarted}
                            className="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white shadow-md hover:shadow-lg hover:shadow-indigo-200 transition-all duration-200"
                        >
                            Get Started
                        </Button>
                    </div>

                    {/* Mobile hamburger */}
                    <button
                        className="md:hidden p-2 rounded-lg text-slate-700 hover:bg-slate-100 transition-colors"
                        onClick={() => setMenuOpen((prev) => !prev)}
                        aria-label="Toggle menu"
                    >
                        {menuOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
                    </button>
                </div>

                {/* Mobile dropdown */}
                {menuOpen && (
                    <div className="md:hidden bg-white/98 backdrop-blur-md border-t border-slate-100 pb-4">
                        <nav className="pt-2 space-y-0.5">
                            {NAV_LINKS.map((item) => (
                                <button
                                    key={item.id}
                                    onClick={() => scrollTo(item.id)}
                                    className="block w-full text-left px-4 py-2.5 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors"
                                >
                                    {item.label}
                                </button>
                            ))}
                        </nav>
                        <div className="mt-3 pt-3 border-t border-slate-100 flex flex-col gap-2 px-2">
                            <Button variant="outline" onClick={onLogin} className="w-full">
                                Log In
                            </Button>
                            <Button
                                onClick={onGetStarted}
                                className="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white hover:from-blue-700 hover:to-indigo-700"
                            >
                                Get Started Free
                            </Button>
                        </div>
                    </div>
                )}
            </div>
        </header>
    );
}
