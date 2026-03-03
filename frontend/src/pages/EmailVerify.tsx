import { useSearchParams, useNavigate } from "react-router-dom";
import { Dumbbell, CircleCheck, CircleAlert } from "lucide-react";
import { Button } from "../components/ui/button";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "../components/ui/card";

export const EmailVerify = () => {
    const [searchParams] = useSearchParams();
    const navigate = useNavigate();
    const status = searchParams.get("status");

    const isSuccess = status === "success";
    const isAlready = status === "alredy";
    const isValid = isSuccess || isAlready;

    const config = isSuccess
        ? {
            icon: <CircleCheck className="w-8 h-8 text-green-600" />,
            iconBg: "bg-green-100",
            title: "Email verified!",
            description:
                "Your email address has been successfully verified. You're all set to start your fitness journey.",
        }
        : isAlready
            ? {
                icon: <CircleCheck className="w-8 h-8 text-blue-600" />,
                iconBg: "bg-blue-100",
                title: "Already verified",
                description:
                    "Your email address was already verified. You can go straight to your dashboard.",
            }
            : {
                icon: <CircleAlert className="w-8 h-8 text-red-500" />,
                iconBg: "bg-red-100",
                title: "Invalid link",
                description:
                    "This verification link is not valid or has expired. Please try signing in and request a new one.",
            };

    return (
        <div className="relative z-10 w-full max-w-md">
            {/* Logo */}
            <div className="flex items-center justify-center gap-2 mb-8">
                <div className="w-12 h-12 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                    <Dumbbell className="w-7 h-7 text-white" />
                </div>
                <span className="text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                    Smart Move AI
                </span>
            </div>

            <Card className="border-0 shadow-2xl">
                <CardHeader className="space-y-3 items-center text-center">
                    <div className={`w-16 h-16 ${config.iconBg} rounded-full flex items-center justify-center`}>
                        {config.icon}
                    </div>
                    <CardTitle className="text-2xl">{config.title}</CardTitle>
                    <CardDescription className="text-base">{config.description}</CardDescription>
                </CardHeader>

                <CardContent className="flex flex-col gap-3">
                    {isValid ? (
                        <Button
                            className="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700"
                            onClick={() => navigate("/dashboard")}
                        >
                            Go to dashboard
                        </Button>
                    ) : (
                        <Button
                            variant="outline"
                            className="w-full"
                            onClick={() => navigate("/login")}
                        >
                            Back to sign in
                        </Button>
                    )}
                </CardContent>
            </Card>
        </div>
    );
};
