import { useState, useEffect } from "react";
import { Link } from "react-router";
import {
    Users,
    MessageSquare,
    Star,
    ChevronRight,
    ShieldCheck,
} from "lucide-react";
import { Card, CardContent } from "@/components/ui/card";
import { useAuth } from "@/contexts/AuthContext";
import { getAdminUsers, getAdminFeedbacks, type PaginatedResponse } from "@/services/admin";
import type { User } from "@/types/auth";
import type { Feedback } from "@/types/feedback";

const getGreeting = () => {
    const h = new Date().getHours();
    if (h < 12) return "Good morning";
    if (h < 17) return "Good afternoon";
    if (h < 21) return "Good evening";
    return "Good night";
};

interface StatCardProps {
    icon: React.ReactNode;
    iconBg: string;
    label: string;
    value: string | number;
    href?: string;
}

const StatCard = ({ icon, iconBg, label, value, href }: StatCardProps) => {
    const content = (
        <Card className="hover:shadow-md transition-shadow">
            <CardContent className="p-5 flex items-center gap-4">
                <div className={`w-10 h-10 ${iconBg} rounded-xl flex items-center justify-center flex-shrink-0`}>
                    {icon}
                </div>
                <div>
                    <p className="text-xs text-slate-500 font-medium">{label}</p>
                    <p className="text-2xl font-bold text-slate-900">{value}</p>
                </div>
            </CardContent>
        </Card>
    );

    if (href) {
        return <Link to={href}>{content}</Link>;
    }

    return content;
};

export const AdminDashboard = () => {
    const { user } = useAuth();
    const [usersData, setUsersData] = useState<PaginatedResponse<User> | null>(null);
    const [feedbacksData, setFeedbacksData] = useState<PaginatedResponse<Feedback> | null>(null);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        Promise.all([getAdminUsers(1, 5), getAdminFeedbacks(1, 5)])
            .then(([users, feedbacks]) => {
                setUsersData(users);
                setFeedbacksData(feedbacks);
            })
            .finally(() => setIsLoading(false));
    }, []);

    const ratedFeedbacks = feedbacksData?.data?.filter((f) => f.rating !== null) ?? [];
    const avgRating =
        ratedFeedbacks.length > 0
            ? (ratedFeedbacks.reduce((sum, f) => sum + (f.rating ?? 0), 0) / ratedFeedbacks.length).toFixed(1)
            : "—";

    return (
        <div className="mx-auto space-y-6">
            {/* Greeting */}
            <section className="animate-fade-in-up relative rounded-2xl overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-900 p-6 sm:p-8">
                <div className="absolute top-0 right-0 w-64 h-64 bg-indigo-500/10 rounded-full -translate-y-1/2 translate-x-1/4 pointer-events-none" />
                <div className="relative">
                    <div className="flex items-center gap-2 mb-1">
                        <ShieldCheck className="w-4 h-4 text-indigo-300" />
                        <p className="text-indigo-300 text-sm font-medium">{getGreeting()}, Admin</p>
                    </div>
                    <h1 className="text-2xl sm:text-3xl font-bold text-white mb-1">
                        {user?.name} {user?.surname}
                    </h1>
                    <p className="text-slate-400 text-sm">
                        {new Date().toLocaleDateString("en-US", {
                            weekday: "long",
                            month: "long",
                            day: "numeric",
                        })}
                    </p>
                </div>
            </section>

            {/* Stats */}
            <section className="grid grid-cols-1 sm:grid-cols-3 gap-4 animate-fade-in-up" style={{ animationDelay: "75ms" }}>
                <StatCard
                    icon={<Users className="w-5 h-5 text-indigo-600" />}
                    iconBg="bg-indigo-50"
                    label="Total Users"
                    value={isLoading ? "…" : (usersData?.meta.total ?? 0)}
                    href="/dashboard/admin/users"
                />
                <StatCard
                    icon={<MessageSquare className="w-5 h-5 text-blue-600" />}
                    iconBg="bg-blue-50"
                    label="Total Feedbacks"
                    value={isLoading ? "…" : (feedbacksData?.meta.total ?? 0)}
                    href="/dashboard/admin/feedbacks"
                />
                <StatCard
                    icon={<Star className="w-5 h-5 text-yellow-500" />}
                    iconBg="bg-yellow-50"
                    label="Avg. Rating"
                    value={isLoading ? "…" : avgRating}
                />
            </section>

            {/* Quick links */}
            <section className="grid grid-cols-1 sm:grid-cols-2 gap-4 animate-fade-in-up" style={{ animationDelay: "150ms" }}>
                <Link to="/dashboard/admin/users">
                    <div className="group rounded-xl border border-slate-200 bg-white p-5 cursor-pointer hover:shadow-md hover:border-slate-300 transition-all duration-200 h-full">
                        <div className="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center mb-3">
                            <Users className="w-5 h-5 text-indigo-600" />
                        </div>
                        <h3 className="text-base font-semibold text-slate-900 mb-1">Manage Users</h3>
                        <p className="text-sm text-slate-500 mb-3">View and manage all registered users</p>
                        <div className="flex items-center gap-1 text-indigo-600 text-xs font-medium">
                            <span>View all</span>
                            <ChevronRight className="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" />
                        </div>
                    </div>
                </Link>

                <Link to="/dashboard/admin/feedbacks">
                    <div className="group rounded-xl border border-slate-200 bg-white p-5 cursor-pointer hover:shadow-md hover:border-slate-300 transition-all duration-200 h-full">
                        <div className="w-10 h-10 bg-yellow-50 rounded-xl flex items-center justify-center mb-3">
                            <MessageSquare className="w-5 h-5 text-yellow-600" />
                        </div>
                        <h3 className="text-base font-semibold text-slate-900 mb-1">User Feedbacks</h3>
                        <p className="text-sm text-slate-500 mb-3">Read and review all user feedback submissions</p>
                        <div className="flex items-center gap-1 text-yellow-600 text-xs font-medium">
                            <span>View all</span>
                            <ChevronRight className="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" />
                        </div>
                    </div>
                </Link>
            </section>

            {/* Recent feedbacks preview */}
            {!isLoading && feedbacksData && feedbacksData.data.length > 0 && (
                <section className="animate-fade-in-up" style={{ animationDelay: "225ms" }}>
                    <div className="flex items-center justify-between mb-3">
                        <h2 className="text-sm font-semibold text-slate-900">Recent Feedbacks</h2>
                        <Link
                            to="/dashboard/admin/feedbacks"
                            className="text-xs text-indigo-600 hover:text-indigo-700 font-medium flex items-center gap-0.5"
                        >
                            View all <ChevronRight className="w-3.5 h-3.5" />
                        </Link>
                    </div>
                    <div className="space-y-2">
                        {feedbacksData.data.slice(0, 3).map((feedback) => (
                            <div
                                key={feedback.id}
                                className="bg-white border border-slate-200 rounded-xl p-4 space-y-1.5"
                            >
                                <div className="flex items-center justify-between">
                                    <p className="text-sm font-medium text-slate-900">
                                        {feedback.user
                                            ? `${feedback.user.name} ${feedback.user.surname}`
                                            : `User #${feedback.user_id}`}
                                    </p>
                                    <div className="flex items-center gap-1">
                                        {feedback.rating !== null && (
                                            <>
                                                <Star className="w-3.5 h-3.5 text-yellow-400 fill-yellow-400" />
                                                <span className="text-xs font-semibold text-slate-700">
                                                    {feedback.rating}
                                                </span>
                                            </>
                                        )}
                                    </div>
                                </div>
                                {feedback.message && (
                                    <p className="text-sm text-slate-500 line-clamp-2">{feedback.message}</p>
                                )}
                            </div>
                        ))}
                    </div>
                </section>
            )}
        </div>
    );
};
