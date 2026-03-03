import { createBrowserRouter } from "react-router-dom";
import { AuthLayout } from "../layouts/AuthLayout";
import { DashboardLayout } from "../layouts/DashboardLayout";
import { Register } from "../pages/Register";
import { Login } from "../pages/Login";
import { WelcomePage } from "../pages/Welcome";
import { Dashboard } from "../pages/Dashboard";
import { ForgotPassword } from "../pages/ForgotPassword";

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
        ],
    },
    {
        element: <DashboardLayout />,
        children: [
            { path: "/dashboard", element: <Dashboard /> },
        ],
    },
]);
