import { useState } from "react";
import { Link } from "react-router";
import { Cookie } from "lucide-react";
import { Button } from "@/components/ui/button";

const STORAGE_KEY = "cookie-consent";

export function CookieBanner() {
    const [dismissed, setDismissed] = useState(
        () => localStorage.getItem(STORAGE_KEY) !== null,
    );

    if (dismissed) return null;

    const accept = () => {
        localStorage.setItem(STORAGE_KEY, "accepted");
        setDismissed(true);
    };

    return (
        <div className="fixed bottom-0 left-0 right-0 z-50 p-4 sm:p-6">
            <div className="max-w-4xl mx-auto bg-slate-900 text-white rounded-2xl shadow-2xl border border-slate-700 px-5 py-4 flex flex-col sm:flex-row items-start sm:items-center gap-4">
                <div className="flex items-start gap-3 flex-1 min-w-0">
                    <Cookie className="w-5 h-5 text-amber-400 flex-shrink-0 mt-0.5" />
                    <p className="text-sm text-slate-300 leading-relaxed">
                        We use strictly necessary cookies to keep you logged in. No tracking or
                        advertising cookies are used. See our{" "}
                        <Link to="/privacy" className="text-blue-400 hover:text-blue-300 underline underline-offset-2">
                            Privacy Policy
                        </Link>{" "}
                        for details.
                    </p>
                </div>
                <Button
                    onClick={accept}
                    size="sm"
                    className="flex-shrink-0 bg-white text-slate-900 hover:bg-slate-100 font-medium"
                >
                    Got it
                </Button>
            </div>
        </div>
    );
}
