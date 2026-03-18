import { Link } from "react-router-dom";
import { Dumbbell, ArrowLeft } from "lucide-react";
import { Button } from "@/components/ui/button";
import { useAuth } from "@/contexts/AuthContext";

export const NotFound = () => {
    const { isAuthenticated } = useAuth();
    const homeHref = isAuthenticated ? "/dashboard" : "/";

    return (
        <div className="min-h-screen bg-slate-50 flex flex-col items-center justify-center px-4">
            {/* Logo */}
            <div className="flex items-center gap-2 mb-12">
                <div className="w-8 h-8 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
                    <Dumbbell className="w-4 h-4 text-white" />
                </div>
                <span className="font-bold text-slate-900 text-lg">SmartMove</span>
            </div>

            {/* 404 block */}
            <div className="text-center max-w-md">
                <p className="text-8xl font-extrabold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent leading-none mb-4">
                    404
                </p>
                <h1 className="text-2xl font-bold text-slate-900 mb-2">Page not found</h1>
                <p className="text-slate-500 text-sm mb-8">
                    The page you're looking for doesn't exist or has been moved.
                </p>

                <Link to={homeHref}>
                    <Button className="bg-gradient-to-r from-blue-600 to-indigo-600 text-white gap-2">
                        <ArrowLeft className="w-4 h-4" />
                        {isAuthenticated ? "Back to Dashboard" : "Back to Home"}
                    </Button>
                </Link>
            </div>
        </div>
    );
};
