import { useState } from "react";
import { Link, useLocation, Outlet } from "react-router-dom";
import {
    Home,
    Dumbbell,
    TrendingUp,
    Apple,
    Camera,
    Timer,
    Trophy,
    Menu,
    X,
    Settings,
    MessageSquare,
} from "lucide-react";
import { Avatar, AvatarFallback } from "../ui/avatar";
import { useAuth } from "../../contexts/AuthContext";

const navigation = [
    {
        name: "Dashboard",
        href: "/dashboard",
        icon: Home
    },
    {
        name: "Generate Workout",
        href: "/dashboard/workout-generate",
        icon: MessageSquare
    },
    {
        name: "Workouts",
        href: "/dashboard/workouts",
        icon: Dumbbell,
    },
    /*{
        name: "Progress",
        href: "/dashboard/progress",
        icon: TrendingUp,
    },
    {
        name: "Nutrition",
        href: "/dashboard/nutrition",
        icon: Apple,
    },
    {
        name: "Body Tracking",
        href: "/dashboard/body",
        icon: Camera,
    },
    { name: "Gym Mode", href: "/dashboard/gym", icon: Timer },
    {
        name: "Achievements",
        href: "/dashboard/achievements",
        icon: Trophy,
    },*/
];

export function SideBar() {
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const location = useLocation();
    const { user } = useAuth();
    const userInitial = user?.name?.charAt(0).toUpperCase() ?? "?";

    return (
        <div className="min-h-screen bg-slate-50">
            {/* Mobile sidebar */}
            {sidebarOpen && (
                <div className="fixed inset-0 z-50 lg:hidden">
                    <div
                        className="fixed inset-0 bg-slate-900/50"
                        onClick={() => setSidebarOpen(false)}
                    />
                    <div className="fixed inset-y-0 left-0 w-64 bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 shadow-xl">
                        <div className="flex items-center justify-between p-4 border-b border-slate-700">
                            <div className="flex items-center gap-2">
                                <div className="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-lg flex items-center justify-center shadow-lg">
                                    <Dumbbell className="w-5 h-5 text-white" />
                                </div>
                                <span className="font-bold text-white">
                                    Smart Move AI
                                </span>
                            </div>
                            <button onClick={() => setSidebarOpen(false)}>
                                <X className="w-6 h-6 text-slate-400 hover:text-white" />
                            </button>
                        </div>
                        <nav className="p-4 space-y-2">
                            {navigation.map((item) => {
                                const Icon = item.icon;
                                const isActive =
                                    location.pathname === item.href;
                                return (
                                    <Link
                                        key={item.name}
                                        to={item.href}
                                        onClick={() => setSidebarOpen(false)}
                                        className={`flex items-center gap-3 px-4 py-3 rounded-lg transition-all ${isActive
                                            ? "bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/50"
                                            : "text-slate-300 hover:bg-slate-800 hover:text-white"
                                            }`}
                                    >
                                        <Icon className="w-5 h-5" />
                                        <span className="font-medium">
                                            {item.name}
                                        </span>
                                    </Link>
                                );
                            })}
                        </nav>
                    </div>
                </div>
            )}

            {/* Desktop sidebar */}
            <div className="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col">
                <div className="flex flex-col flex-1 min-h-0 bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 border-r border-slate-700 shadow-xl">
                    <div className="flex items-center gap-2 px-6 py-5 border-b border-slate-700">
                        <div className="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center shadow-lg">
                            <Dumbbell className="w-6 h-6 text-white" />
                        </div>
                        <span className="text-lg font-bold text-white">
                            Smart Move AI
                        </span>
                    </div>
                    <nav className="flex-1 p-4 space-y-2">
                        {navigation.map((item) => {
                            const Icon = item.icon;
                            const isActive = location.pathname === item.href;
                            return (
                                <Link
                                    key={item.name}
                                    to={item.href}
                                    className={`flex items-center gap-3 px-4 py-3 rounded-lg transition-all ${isActive
                                        ? "bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/50"
                                        : "text-slate-300 hover:bg-slate-800 hover:text-white"
                                        }`}
                                >
                                    <Icon className="w-5 h-5" />
                                    <span className="font-medium">
                                        {item.name}
                                    </span>
                                </Link>
                            );
                        })}
                    </nav>
                    <div className="p-4 border-t border-slate-700">
                        <Link
                            to="/dashboard/profile"
                            className="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-all"
                        >
                            <Settings className="w-5 h-5" />
                            <span className="font-medium">Settings</span>
                        </Link>
                    </div>
                </div>
            </div>

            {/* Main content */}
            <div className="lg:pl-64 flex flex-col min-h-screen">
                {/* Top bar */}
                <header className="sticky top-0 z-40 bg-white border-2 ">
                    <div className="flex items-center justify-between px-4 py-3">
                        <button
                            onClick={() => setSidebarOpen(true)}
                            className="lg:hidden p-2 rounded-lg hover:bg-slate-100"
                        >
                            <Menu className="w-6 h-6" />
                        </button>

                        <div className="flex-1 lg:flex-none"></div>

                        <div className="flex items-center gap-4">
                            <Link to="/dashboard/profile">
                                <Avatar className="cursor-pointer ring-2 ring-blue-100 hover:ring-blue-200">
                                    <AvatarFallback className="bg-gradient-to-br from-blue-600 to-indigo-600 text-white font-semibold">
                                        {userInitial}
                                    </AvatarFallback>
                                </Avatar>
                            </Link>
                        </div>
                    </div>
                </header>

                {/* Page content */}
                <main className="flex-1 p-6">
                    <Outlet />
                </main>
            </div>
        </div>
    );
}