import { Outlet } from "react-router-dom";

export const AuthLayout = () => {
    return (
        <div className="relative min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 p-4">
            <div className="absolute inset-0 bg-grid-slate-900/[0.04] bg-[size:32px_32px]" />
            <div className="absolute top-20 left-10 w-72 h-72 bg-blue-400/20 rounded-full blur-3xl animate-pulse" />
            <div className="absolute bottom-20 right-10 w-96 h-96 bg-indigo-400/20 rounded-full blur-3xl animate-pulse delay-1000" />
            <Outlet />
        </div>
    );
};
