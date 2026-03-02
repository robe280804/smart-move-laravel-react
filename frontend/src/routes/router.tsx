import { createBrowserRouter } from "react-router-dom";
import { AuthLayout } from "../layouts/AuthLayout";
import { Register } from "../pages/Register";
import { Login } from "../pages/Login";
import { WelcomePage } from "../pages/Welcome";

export const router = createBrowserRouter([
    {
        element: <AuthLayout />,
        children: [
            { path: "/welcome", element: <WelcomePage /> },
            { path: "/register", element: <Register /> },
            { path: "/login", element: <Login /> },
            /*{ path: "/reset-password", element: <ResetPassword /> },
            { path: "/verify-email", element: <VerifyEmail /> },*/
        ],
    },
]);
