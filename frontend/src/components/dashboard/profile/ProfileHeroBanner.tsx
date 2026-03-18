import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { Button } from "@/components/ui/button";
import { LogOut, Ruler, Settings, Trophy, User, Weight } from "lucide-react";
import type { User as UserType } from "@/types/auth";
import type { FitnessInfo } from "@/types/user";

interface ProfileHeroBannerProps {
    user: UserType | null;
    fitnessInfo: FitnessInfo | null;
    onLogout: () => void;
}

export function ProfileHeroBanner({ user, fitnessInfo, onLogout }: ProfileHeroBannerProps) {
    const initials = user
        ? `${user.name.charAt(0)}${user.surname.charAt(0)}`.toUpperCase()
        : "?";

    const fullName = user ? `${user.name} ${user.surname}` : "";

    return (
        <div className="relative rounded-2xl overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-900 p-6">
            <div className="absolute top-0 right-0 w-64 h-64 bg-indigo-500/10 rounded-full -translate-y-1/2 translate-x-1/4 pointer-events-none" />
            <div className="absolute bottom-0 left-1/3 w-40 h-40 bg-blue-500/10 rounded-full translate-y-1/2 pointer-events-none" />

            <div className="relative flex flex-col sm:flex-row sm:items-center justify-between gap-5">
                <div>
                    <div className="flex items-center gap-2 mb-2">
                        <div className="w-8 h-8 rounded-lg bg-indigo-500/20 flex items-center justify-center">
                            <Settings className="w-4 h-4 text-indigo-300" />
                        </div>
                        <span className="text-indigo-300 text-xs font-semibold uppercase tracking-widest">
                            Account
                        </span>
                    </div>
                    <h1 className="text-2xl font-bold text-white mb-1">Profile & Settings</h1>
                    <p className="text-slate-400 text-sm">
                        Manage your personal info, fitness data, and preferences
                    </p>
                </div>

                <div className="flex items-center gap-3 flex-shrink-0">
                    <Avatar className="w-14 h-14 ring-2 ring-white/20">
                        <AvatarFallback className="bg-gradient-to-br from-blue-500 to-indigo-600 text-white text-lg font-bold">
                            {initials}
                        </AvatarFallback>
                    </Avatar>
                    <div>
                        <p className="text-white font-semibold leading-tight">{fullName}</p>
                        <p className="text-slate-400 text-xs">{user?.email}</p>
                    </div>
                </div>
            </div>

            <div className="relative flex flex-wrap items-center gap-6 mt-5 pt-5 border-t border-white/10">
                {fitnessInfo && (
                    <>
                        <div className="flex items-center gap-2 text-slate-300">
                            <Ruler className="w-4 h-4 text-indigo-400" />
                            <span className="text-sm">
                                <span className="font-semibold text-white">{fitnessInfo.height}</span>{" "}cm
                            </span>
                        </div>
                        <div className="flex items-center gap-2 text-slate-300">
                            <Weight className="w-4 h-4 text-indigo-400" />
                            <span className="text-sm">
                                <span className="font-semibold text-white">{fitnessInfo.weight}</span>{" "}kg
                            </span>
                        </div>
                        {fitnessInfo.age && (
                            <div className="flex items-center gap-2 text-slate-300">
                                <User className="w-4 h-4 text-indigo-400" />
                                <span className="text-sm">
                                    <span className="font-semibold text-white">{fitnessInfo.age}</span>{" "}years old
                                </span>
                            </div>
                        )}
                        {fitnessInfo.experience_level && (
                            <div className="flex items-center gap-2 text-slate-300">
                                <Trophy className="w-4 h-4 text-indigo-400" />
                                <span className="text-sm font-semibold text-white">
                                    {fitnessInfo.experience_level.charAt(0).toUpperCase() + fitnessInfo.experience_level.slice(1)}
                                </span>
                            </div>
                        )}
                    </>
                )}
                <Button
                    variant="outline"
                    size="sm"
                    onClick={onLogout}
                    className="ml-auto border-white/20 text-white bg-white/10 hover:bg-red-500/20 hover:border-red-400/40 hover:text-red-300 transition-colors"
                >
                    <LogOut className="w-4 h-4 mr-2" />
                    Log Out
                </Button>
            </div>
        </div>
    );
}
