export function DashboardSkeleton() {
    return (
        <div className="mx-auto space-y-6">
            {/* Hero skeleton */}
            <div className="rounded-2xl bg-slate-100 h-28 animate-pulse" />

            {/* Stats skeleton */}
            <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
                {Array.from({ length: 4 }).map((_, i) => (
                    <div
                        key={i}
                        className="rounded-xl border bg-white h-[108px] animate-pulse"
                    >
                        <div className="p-5 space-y-3">
                            <div className="w-10 h-10 bg-slate-100 rounded-xl" />
                            <div className="space-y-1.5">
                                <div className="h-6 w-12 bg-slate-100 rounded" />
                                <div className="h-3 w-20 bg-slate-50 rounded" />
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            {/* Quick actions skeleton */}
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div className="rounded-xl bg-slate-200 h-[148px] animate-pulse" />
                <div className="rounded-xl border bg-white h-[148px] animate-pulse" />
            </div>

            {/* Content skeleton */}
            <div className="grid grid-cols-1 lg:grid-cols-5 gap-6">
                <div className="lg:col-span-3 space-y-2">
                    <div className="h-4 w-24 bg-slate-100 rounded mb-3" />
                    {Array.from({ length: 3 }).map((_, i) => (
                        <div
                            key={i}
                            className="rounded-xl border bg-white h-[60px] animate-pulse"
                        />
                    ))}
                </div>
                <div className="lg:col-span-2 space-y-4">
                    <div className="rounded-xl border bg-white h-40 animate-pulse" />
                    <div className="rounded-xl border bg-white h-40 animate-pulse" />
                </div>
            </div>
        </div>
    );
}
