import { Dumbbell } from "lucide-react";
import { FOOTER_LINKS } from "@/constants/welcome";
import { AnimatedSection } from "./AnimatedSection";

export function Footer() {
    return (
        <footer className="bg-slate-900 text-slate-400 py-16">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <AnimatedSection>
                <div className="grid md:grid-cols-4 gap-10 mb-12">
                    {/* Brand */}
                    <div className="space-y-4">
                        <div className="flex items-center gap-2">
                            <div className="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
                                <Dumbbell className="w-6 h-6 text-white" />
                            </div>
                            <span className="text-xl font-bold text-white">Smart Move AI</span>
                        </div>
                        <p className="text-sm leading-relaxed">
                            AI-powered fitness planning for everyone, everywhere.
                        </p>
                    </div>

                    {/* Product */}
                    <div>
                        <h3 className="font-semibold text-white mb-4">Product</h3>
                        <ul className="space-y-2.5 text-sm">
                            {FOOTER_LINKS.product.map((link) => (
                                <li key={link.label}>
                                    <a href={link.href} className="hover:text-white transition-colors">
                                        {link.label}
                                    </a>
                                </li>
                            ))}
                        </ul>
                    </div>

                    {/* Company */}
                    <div>
                        <h3 className="font-semibold text-white mb-4">Company</h3>
                        <ul className="space-y-2.5 text-sm">
                            {FOOTER_LINKS.company.map((link) => (
                                <li key={link.label}>
                                    <a href={link.href} className="hover:text-white transition-colors">
                                        {link.label}
                                    </a>
                                </li>
                            ))}
                        </ul>
                    </div>

                    {/* Support */}
                    <div>
                        <h3 className="font-semibold text-white mb-4">Support</h3>
                        <ul className="space-y-2.5 text-sm">
                            {FOOTER_LINKS.support.map((link) => (
                                <li key={link.label}>
                                    <a href={link.href} className="hover:text-white transition-colors">
                                        {link.label}
                                    </a>
                                </li>
                            ))}
                        </ul>
                    </div>
                </div>

                <div className="border-t border-slate-800 pt-8 text-center text-sm">
                    <p>&copy; 2026 Smart Move AI. All rights reserved.</p>
                </div>
                </AnimatedSection>
            </div>
        </footer>
    );
}
