import type { ReactNode } from "react";
import { Link } from "react-router";
import { ChevronRight } from "lucide-react";
import { Card, CardContent } from "@/components/ui/card";

interface StatCardProps {
    icon: ReactNode;
    iconBg: string;
    label: string;
    value: string | number;
    link?: string;
}

export function StatCard({ icon, iconBg, label, value, link }: StatCardProps) {
    const inner = (
        <Card
            className={`py-0 group transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 ${
                link ? "cursor-pointer" : ""
            }`}
        >
            <CardContent className="p-5">
                <div className="flex items-center justify-between mb-3">
                    <div
                        className={`w-10 h-10 ${iconBg} rounded-xl flex items-center justify-center`}
                    >
                        {icon}
                    </div>
                    {link && (
                        <ChevronRight className="w-4 h-4 text-slate-300 group-hover:text-indigo-500 transition-colors" />
                    )}
                </div>
                <p className="text-xl sm:text-2xl font-bold text-slate-900 truncate">{value}</p>
                <p className="text-xs text-slate-500 mt-0.5">{label}</p>
            </CardContent>
        </Card>
    );

    if (link) {
        return (
            <Link to={link} className="block">
                {inner}
            </Link>
        );
    }

    return inner;
}
