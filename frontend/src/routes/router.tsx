import { createBrowserRouter } from "react-router-dom";
import { AuthLayout } from "../layouts/AuthLayout";
import { SideBar } from "../components/dashboard/SideBar";
import { Register } from "../pages/Register";
import { Login } from "../pages/Login";
import { WelcomePage } from "../pages/Welcome";
import { Dashboard } from "../pages/Dashboard";
import { ForgotPassword } from "../pages/ForgotPassword";
import { ResetPassword } from "../pages/ResetPassword";
import { EmailVerify } from "../pages/EmailVerify";
import { ProtectedRoute } from "../layouts/ProtectedRoute";
import { ProfileAndSettings } from "../pages/dashboard/ProfileAndSettings";
import { WorkoutPlanGenerator } from "@/pages/dashboard/WorkoutPlanGenerator";
import { Workouts } from "@/pages/dashboard/Workouts";
import { WorkoutPlanDetail } from "@/pages/dashboard/WorkoutPlanDetail";

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
]);
