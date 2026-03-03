import { createBrowserRouter } from "react-router-dom";
import { AuthLayout } from "../layouts/AuthLayout";
import { DashboardLayout } from "../layouts/DashboardLayout";
import { Register } from "../pages/Register";
import { Login } from "../pages/Login";
import { WelcomePage } from "../pages/Welcome";
import { Dashboard } from "../pages/Dashboard";
import { ForgotPassword } from "../pages/ForgotPassword";
import { ResetPassword } from "../pages/ResetPassword";
import { EmailVerify } from "../pages/EmailVerify";
import { ProtectedRoute } from "../layouts/ProtectedRoute";

export const router = createBrowserRouter([
    {
        path: "/",
        element: <WelcomePage />,
    },
    {
        element: <AuthLayout />,
        children: [
            { path: "/register", element: <Register /> },
            { path: "/login", element: <Login /> },
            { path: "/forgot-password", element: <ForgotPassword /> },
            { path: "/reset-password", element: <ResetPassword /> },
            { path: "/email-verify", element: <EmailVerify /> },
        ],
    },
    {
        element: <ProtectedRoute />,
        children: [
            {
                element: <DashboardLayout />,
                children: [
                    { path: "/dashboard", element: <Dashboard /> },
                ],
            }
        ]

    },
]);
