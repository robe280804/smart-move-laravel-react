import { Video, Sparkles, Check } from "lucide-react";

const HIGHLIGHTS = [
    "Real-time form correction",
    "Rep counting & tempo tracking",
    "Personalized technique tips",
    "Works with any exercise",
] as const;

export function ComingSoonSection() {
    return (
        <section className="animate-fade-in-up rounded-2xl overflow-hidden border border-violet-200 bg-white">
            {/* Header strip */}
            <div className="flex items-center gap-2 px-5 py-3 bg-violet-50 border-b border-violet-100">
                <span className="relative flex h-2 w-2">
                    <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-violet-400 opacity-75" />
                    <span className="relative inline-flex rounded-full h-2 w-2 bg-violet-500" />
                </span>
                <p className="text-xs font-semibold text-violet-700 uppercase tracking-widest">
                    In Development
                </p>
            </div>

            <div className="p-5 sm:p-6 flex flex-col sm:flex-row gap-6 sm:items-center">
                {/* Icon */}
                <div className="relative flex-shrink-0 self-start">
                    <div className="w-16 h-16 rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-lg shadow-violet-500/25">
                        <Video className="w-7 h-7 text-white" />
                    </div>
                    <div className="absolute -top-1.5 -right-1.5 w-6 h-6 rounded-full bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center shadow-sm">
                        <Sparkles className="w-3 h-3 text-white" />
                    </div>
                </div>

                {/* Content */}
                <div className="flex-1 min-w-0">
                    <h2 className="text-base font-bold text-slate-900 mb-1">
                        AI Exercise Video Analysis
                    </h2>
                    <p className="text-sm text-slate-500 mb-4 leading-relaxed">
                        Record yourself doing any exercise and let AI analyze your
                        form in real time — detecting mistakes, counting reps, and
                        coaching you toward perfect technique.
                    </p>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                        {HIGHLIGHTS.map((item) => (
                            <div key={item} className="flex items-center gap-2">
                                <div className="w-4 h-4 rounded-full bg-violet-100 flex items-center justify-center flex-shrink-0">
                                    <Check className="w-2.5 h-2.5 text-violet-600" />
                                </div>
                                <span className="text-xs text-slate-600">{item}</span>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </section>
    );
}
