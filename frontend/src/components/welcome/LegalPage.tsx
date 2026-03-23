import { Link } from "react-router";
import { ArrowLeft, Dumbbell } from "lucide-react";

type Section = {
    title: string;
    content: React.ReactNode;
};

type Props = {
    title: string;
    subtitle: string;
    lastUpdated: string;
    sections: Section[];
};

export function LegalPage({ title, subtitle, lastUpdated, sections }: Props) {
    return (
        <div className="min-h-screen bg-slate-50">
            {/* Header */}
            <header className="bg-white border-b border-slate-200 sticky top-0 z-10">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
                    <Link to="/" className="flex items-center gap-2">
                        <div className="w-8 h-8 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
                            <Dumbbell className="w-4 h-4 text-white" />
                        </div>
                        <span className="font-bold text-slate-900">Smart Move AI</span>
                    </Link>
                    <Link
                        to="/"
                        className="flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-900 transition-colors"
                    >
                        <ArrowLeft className="w-4 h-4" />
                        Back to Home
                    </Link>
                </div>
            </header>

            {/* Content */}
            <main className="max-w-4xl mx-auto px-4 sm:px-6 py-12">
                {/* Page title */}
                <div className="mb-10">
                    <h1 className="text-3xl font-bold text-slate-900 mb-2">{title}</h1>
                    <p className="text-slate-500">{subtitle}</p>
                    <p className="text-sm text-slate-400 mt-1">Last updated: {lastUpdated}</p>
                </div>

                {/* Sections */}
                <div className="space-y-8">
                    {sections.map((section, i) => (
                        <section key={i} className="bg-white rounded-xl border border-slate-200 p-6 sm:p-8">
                            <h2 className="text-lg font-semibold text-slate-900 mb-4">
                                {i + 1}. {section.title}
                            </h2>
                            <div className="text-sm text-slate-600 leading-relaxed space-y-3">
                                {section.content}
                            </div>
                        </section>
                    ))}
                </div>

                {/* Footer note */}
                <div className="mt-10 text-center text-sm text-slate-400">
                    <p>
                        Questions? Contact us at{" "}
                        <a href="mailto:privacy@smartmoveai.com" className="text-blue-600 hover:underline">
                            privacy@smartmoveai.com
                        </a>
                    </p>
                </div>
            </main>
        </div>
    );
}
