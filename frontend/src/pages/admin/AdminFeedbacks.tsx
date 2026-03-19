import { useState, useEffect } from "react";
import { MessageSquare, Star, ChevronLeft, ChevronRight } from "lucide-react";
import { Card, CardContent } from "@/components/ui/card";
import { getAdminFeedbacks, type PaginatedResponse } from "@/services/admin";
import type { Feedback } from "@/types/feedback";
import { notify } from "@/lib/toast";

const StarRating = ({ rating }: { rating: number | null }) => {
    if (rating === null) return <span className="text-xs text-slate-400">No rating</span>;
    return (
        <div className="flex items-center gap-0.5">
            {[1, 2, 3, 4, 5].map((star) => (
                <Star
                    key={star}
                    className={`w-3.5 h-3.5 ${
                        star <= rating ? "text-yellow-400 fill-yellow-400" : "text-slate-200"
                    }`}
                />
            ))}
        </div>
    );
};

export const AdminFeedbacks = () => {
    const [data, setData] = useState<PaginatedResponse<Feedback> | null>(null);
    const [page, setPage] = useState(1);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        let cancelled = false;

        getAdminFeedbacks(page)
            .then((result) => {
                if (!cancelled) setData(result);
            })
            .catch(() => {
                if (!cancelled) notify.error("Failed to load feedbacks.");
            })
            .finally(() => {
                if (!cancelled) setIsLoading(false);
            });

        return () => {
            cancelled = true;
        };
    }, [page]);

    const goToPage = (next: number) => {
        setPage(next);
        setIsLoading(true);
    };

    const ratedFeedbacks = data?.data.filter((f) => f.rating !== null) ?? [];
    const avgRating =
        ratedFeedbacks.length > 0
            ? (ratedFeedbacks.reduce((sum, f) => sum + (f.rating ?? 0), 0) / ratedFeedbacks.length).toFixed(1)
            : null;

    return (
        <div className="space-y-6">
            <div className="flex items-center justify-between">
                <div className="flex items-center gap-3">
                    <div className="w-10 h-10 bg-yellow-50 rounded-xl flex items-center justify-center">
                        <MessageSquare className="w-5 h-5 text-yellow-600" />
                    </div>
                    <div>
                        <h1 className="text-xl font-bold text-slate-900">Feedbacks</h1>
                        <p className="text-sm text-slate-500">
                            {data ? `${data.meta.total} feedbacks received` : "Loading…"}
                        </p>
                    </div>
                </div>
                {avgRating && (
                    <div className="flex items-center gap-2 bg-yellow-50 px-4 py-2 rounded-xl">
                        <Star className="w-4 h-4 text-yellow-500 fill-yellow-500" />
                        <span className="text-sm font-semibold text-yellow-700">{avgRating} avg</span>
                    </div>
                )}
            </div>

            <Card>
                <CardContent className="p-0">
                    {isLoading ? (
                        <div className="py-16 text-center text-slate-400 text-sm">Loading…</div>
                    ) : data?.data.length === 0 ? (
                        <div className="py-16 text-center text-slate-400 text-sm">No feedbacks yet.</div>
                    ) : (
                        <div className="divide-y divide-slate-100">
                            {data?.data.map((feedback) => (
                                <div key={feedback.id} className="px-6 py-4 space-y-2">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <p className="text-sm font-medium text-slate-900">
                                                {feedback.user
                                                    ? `${feedback.user.name} ${feedback.user.surname}`
                                                    : `User #${feedback.user_id}`}
                                            </p>
                                            {feedback.user && (
                                                <p className="text-xs text-slate-500">{feedback.user.email}</p>
                                            )}
                                        </div>
                                        <div className="flex items-center gap-3">
                                            <StarRating rating={feedback.rating} />
                                            <span className="text-xs text-slate-400">
                                                {new Date(feedback.created_at).toLocaleDateString("en-US", {
                                                    month: "short",
                                                    day: "numeric",
                                                    year: "numeric",
                                                })}
                                            </span>
                                        </div>
                                    </div>
                                    {feedback.message && (
                                        <p className="text-sm text-slate-600 bg-slate-50 rounded-lg px-3 py-2">
                                            {feedback.message}
                                        </p>
                                    )}
                                </div>
                            ))}
                        </div>
                    )}
                </CardContent>
            </Card>

            {data && data.meta.last_page > 1 && (
                <div className="flex items-center justify-between">
                    <p className="text-sm text-slate-500">
                        Page {data.meta.current_page} of {data.meta.last_page}
                    </p>
                    <div className="flex gap-2">
                        <button
                            onClick={() => goToPage(Math.max(1, page - 1))}
                            disabled={page === 1}
                            className="flex items-center gap-1 px-3 py-1.5 text-sm rounded-lg border border-slate-200 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed"
                        >
                            <ChevronLeft className="w-4 h-4" />
                            Prev
                        </button>
                        <button
                            onClick={() => goToPage(Math.min(data.meta.last_page, page + 1))}
                            disabled={page === data.meta.last_page}
                            className="flex items-center gap-1 px-3 py-1.5 text-sm rounded-lg border border-slate-200 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed"
                        >
                            Next
                            <ChevronRight className="w-4 h-4" />
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
};
