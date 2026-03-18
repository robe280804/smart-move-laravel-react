import { createBrowserRouter } from "react-router-dom";
import { AuthLayout } from "../layouts/AuthLayout";
import { SideBar } from "../components/dashboard/SideBar";
import { Register } from "../pages/auth/Register";
import { Login } from "../pages/auth/Login";
import { WelcomePage } from "../pages/Welcome";
import { Dashboard } from "../pages/Dashboard";
import { ForgotPassword } from "../pages/auth/ForgotPassword";
import { ResetPassword } from "../pages/auth/ResetPassword";
import { EmailVerify } from "../pages/auth/EmailVerify";
import { ProtectedRoute } from "../layouts/ProtectedRoute";
import { ProfileAndSettings } from "../pages/ProfileAndSettings";
import { WorkoutPlanGenerator } from "@/pages/WorkoutPlanGenerator";
import { Workouts } from "@/pages/wokouts/Workouts";
import { WorkoutPlanDetail } from "@/pages/wokouts/WorkoutPlanDetail";
import { NotFound } from "@/pages/NotFound";

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
                element: <SideBar />,
                children: [
                    { path: "/dashboard", element: <Dashboard /> },
                    { path: "/dashboard/profile", element: <ProfileAndSettings /> },
                    { path: "/dashboard/workout-generate", element: <WorkoutPlanGenerator /> },
                    { path: "/dashboard/workouts", element: <Workouts /> },
                    { path: "/dashboard/workouts/:id", element: <WorkoutPlanDetail /> },
                ],
            }
        ]

    },
    {
        path: "*",
        element: <NotFound />,
    },
]);
