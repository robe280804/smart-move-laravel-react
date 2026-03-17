import { Dumbbell, User, Mail } from "lucide-react";
import { Card, CardContent, CardHeader, CardTitle } from "../components/ui/card";
import { useAuth } from "../contexts/AuthContext";

export const Dashboard = () => {
    const { user } = useAuth();

    return (
        <div className="max-w-4xl mx-auto space-y-6">
            <div>
                <h1 className="text-2xl font-bold text-slate-900">
                    Welcome back, {user?.name}!
                </h1>
                <p className="text-slate-500 mt-1">Here's your fitness overview.</p>
            </div>

            {/* User info card */}
            <Card>
                <CardHeader>
                    <CardTitle className="text-base flex items-center gap-2">
                        <User className="w-4 h-4" />
                        Account
                    </CardTitle>
                </CardHeader>
                <CardContent className="space-y-3 text-sm text-slate-700">
                    <div className="flex items-center gap-2">
                        <User className="w-4 h-4 text-slate-400" />
                        <span>{user?.name} {user?.surname}</span>
                    </div>
                    <div className="flex items-center gap-2">
                        <Mail className="w-4 h-4 text-slate-400" />
                        <span>{user?.email}</span>
                    </div>
                </CardContent>
            </Card>

            {/* Placeholder stats */}
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                {[
                    { label: "Workouts this week", value: "—" },
                    { label: "Active streak", value: "—" },
                    { label: "Plans created", value: "—" },
                ].map((stat) => (
                    <Card key={stat.label}>
                        <CardContent className="pt-6 flex flex-col items-center gap-2">
                            <Dumbbell className="w-6 h-6 text-blue-500" />
                            <p className="text-2xl font-bold text-slate-800">{stat.value}</p>
                            <p className="text-xs text-slate-500 text-center">{stat.label}</p>
                        </CardContent>
                    </Card>
                ))}
            </div>
        </div>
    );
};
