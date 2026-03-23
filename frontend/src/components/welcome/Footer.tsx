import { Link } from "react-router";
import { Dumbbell } from "lucide-react";
import { FOOTER_LINKS } from "@/constants/welcome";
import { AnimatedSection } from "./AnimatedSection";

function FooterLink({ label, href }: { label: string; href: string }) {
    const isInternal = href.startsWith("/");
    if (isInternal) {
        return (
            <Link to={href} className="hover:text-white transition-colors">
                {label}
            </Link>
        );
    }
    return (
        <a href={href} className="hover:text-white transition-colors">
            {label}
        </a>
    );
}

export function Footer() {
    return (
        <footer className="bg-slate-900 text-slate-400 py-16">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <AnimatedSection>
                    <div className="grid md:grid-cols-4 gap-10 mb-12">
                        {/* Brand */}
                        <div className="md:col-span-2 space-y-4">
                            <div className="flex items-center gap-2">
                                <div className="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
                                    <Dumbbell className="w-6 h-6 text-white" />
                                </div>
                                <span className="text-xl font-bold text-white">Smart Move AI</span>
                            </div>
                            <p className="text-sm leading-relaxed max-w-xs">
                                AI-powered fitness planning for everyone, everywhere.
                            </p>
                        </div>

                        {/* Product */}
                        <div>
                            <h3 className="font-semibold text-white mb-4">Product</h3>
                            <ul className="space-y-2.5 text-sm">
                                {FOOTER_LINKS.product.map((link) => (
                                    <li key={link.label}>
                                        <FooterLink {...link} />
                                    </li>
                                ))}
                            </ul>
                        </div>

                        {/* Legal */}
                        <div>
                            <h3 className="font-semibold text-white mb-4">Legal</h3>
                            <ul className="space-y-2.5 text-sm">
                                {FOOTER_LINKS.legal.map((link) => (
                                    <li key={link.label}>
                                        <FooterLink {...link} />
                                    </li>
                                ))}
                                {FOOTER_LINKS.company.map((link) => (
                                    <li key={link.label}>
                                        <FooterLink {...link} />
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </div>

                    <div className="border-t border-slate-800 pt-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-sm">
                        <p>&copy; {new Date().getFullYear()} Smart Move AI. All rights reserved.</p>
                        <div className="flex items-center gap-4">
                            <Link to="/privacy" className="hover:text-white transition-colors">
                                Privacy
                            </Link>
                            <Link to="/terms" className="hover:text-white transition-colors">
                                Terms
                            </Link>
                        </div>
                    </div>
                </AnimatedSection>
            </div>
        </footer>
    );
}
