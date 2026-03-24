import { useEffect, useState } from "react";
import { Navigate, Outlet, useNavigate } from "react-router-dom";
import { useAuth } from "../contexts/AuthContext";
import { getAdminUsers } from "@/services/admin";
import { ApiError } from "@/lib/apiError";

export const AdminRoute = () => {
    const { isAuthenticated, isAdmin, isLoading } = useAuth();
    const navigate = useNavigate();
    const [isVerifying, setIsVerifying] = useState(true);

    // Server-side verification: the client-side isAdmin flag is derived from
    // server data, but we also probe an admin endpoint on mount to ensure the
    // backend agrees and to catch any token/role mismatch early.
    useEffect(() => {
        if (isLoading || !isAuthenticated || !isAdmin) {
            setIsVerifying(false);
            return;
        }

        getAdminUsers(1, 1)
            .catch((err) => {
                if (err instanceof ApiError && err.statusCode === 403) {
                    navigate("/dashboard", { replace: true });
                }
            })
            .finally(() => setIsVerifying(false));
    }, [isLoading, isAuthenticated, isAdmin, navigate]);

    if (isLoading || isVerifying) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-slate-50">
                <div className="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin" />
            </div>
        );
    }

    if (!isAuthenticated) {
        return <Navigate to="/login" replace />;
    }

    if (!isAdmin) {
        return <Navigate to="/dashboard" replace />;
    }

    return <Outlet />;
};
