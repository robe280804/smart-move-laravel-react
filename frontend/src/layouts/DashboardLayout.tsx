import { Navigate, Outlet } from "react-router-dom";
import { Dumbbell, LogOut } from "lucide-react";
import { useAuth } from "../contexts/AuthContext";
import { Button } from "../components/ui/button";

export const DashboardLayout = () => {
    const { user, isAuthenticated, isLoading, logout } = useAuth();

    if (isLoading) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-slate-50">
                <div className="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin" />
            </div>
        );
    }

    if (!isAuthenticated) {
        return <Navigate to="/login" replace />;
    }

    return (
        <div className="min-h-screen bg-slate-50 flex flex-col">
            {/* Navbar */}
            <header className="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 shrink-0">
                <div className="flex items-center gap-2">
                    <div className="w-8 h-8 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
                        <Dumbbell className="w-4 h-4 text-white" />
                    </div>
                    <span className="font-bold text-slate-800">Smart Move AI</span>
                </div>

                <div className="flex items-center gap-3">
                    {/* Avatar */}
                    <div className="flex items-center gap-2">
                        <div className="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-500 flex items-center justify-center text-white text-sm font-semibold select-none">
                            {user!.name.charAt(0).toUpperCase()}
                        </div>
                        <span className="text-sm text-slate-700 hidden sm:block">
                            {user!.name} {user!.surname}
                        </span>
                    </div>

                    <Button
                        variant="ghost"
                        size="sm"
                        onClick={logout}
                        className="text-slate-600 hover:text-red-600"
                    >
                        <LogOut className="w-4 h-4 mr-1.5" />
                        Logout
                    </Button>
                </div>
            </header>

            {/* Page content */}
            <main className="flex-1 p-6">
                <Outlet />
            </main>
        </div>
    );
};
