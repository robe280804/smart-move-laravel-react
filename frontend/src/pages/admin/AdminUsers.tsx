import { useState, useEffect, useCallback } from "react";
import { Users, ChevronLeft, ChevronRight, ShieldCheck, User, CheckCircle, XCircle, Pencil, Trash2 } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { getAdminUsers, type PaginatedResponse } from "@/services/admin";
import type { User as UserType } from "@/types/auth";
import { notify } from "@/lib/toast";
import { useAdminUserActions } from "@/hooks/useAdminUserActions";
import { EditUserDialog } from "@/components/admin/EditUserDialog";
import { DeleteUserDialog } from "@/components/admin/DeleteUserDialog";

const PLAN_CONFIG: Record<string, { label: string; className: string }> = {
    pro: {
        label: "Pro",
        className: "bg-gradient-to-r from-purple-500 to-indigo-500 text-white border-0 shadow-sm shadow-purple-200",
    },
    advanced: {
        label: "Advanced",
        className: "bg-gradient-to-r from-blue-500 to-cyan-500 text-white border-0 shadow-sm shadow-blue-200",
    },
    free: {
        label: "Free",
        className: "bg-slate-100 text-slate-500 border-0",
    },
};

const ROLE_CONFIG: Record<string, { label: string; className: string; icon: React.ReactNode }> = {
    admin: {
        label: "Admin",
        className: "bg-indigo-100 text-indigo-700 border-0",
        icon: <ShieldCheck className="w-3 h-3" />,
    },
    user: {
        label: "User",
        className: "bg-slate-100 text-slate-600 border-0",
        icon: <User className="w-3 h-3" />,
    },
};

export const AdminUsers = () => {
    const [data, setData] = useState<PaginatedResponse<UserType> | null>(null);
    const [page, setPage] = useState(1);
    const [isLoading, setIsLoading] = useState(true);

    const fetchUsers = useCallback(() => {
        setIsLoading(true);
        getAdminUsers(page)
            .then((result) => setData(result))
            .catch(() => notify.error("Failed to load users."))
            .finally(() => setIsLoading(false));
    }, [page]);

    useEffect(() => {
        fetchUsers();
    }, [fetchUsers]);

    const {
        editingUser,
        deletingUser,
        form,
        setForm,
        errors,
        isLoading: actionLoading,
        openEdit,
        closeEdit,
        openDelete,
        closeDelete,
        handleUpdate,
        handleDelete,
    } = useAdminUserActions(fetchUsers);

    const goToPage = (next: number) => {
        setPage(next);
    };

    return (
        <div className="space-y-6">
            {/* Header */}
            <div className="flex items-center gap-3">
                <div className="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center">
                    <Users className="w-5 h-5 text-indigo-600" />
                </div>
                <div>
                    <h1 className="text-xl font-bold text-slate-900">Users</h1>
                    <p className="text-sm text-slate-500">
                        {data ? `${data.meta.total} registered users` : "Loading\u2026"}
                    </p>
                </div>
            </div>

            {/* Table */}
            <div className="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                {/* Table header — hidden on mobile */}
                <div className="hidden md:grid md:grid-cols-[2fr_1fr_1fr_1fr_auto] gap-4 px-6 py-3 bg-slate-50 border-b border-slate-200">
                    <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider">User</span>
                    <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Verified</span>
                    <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Plan</span>
                    <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Role</span>
                    <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider w-24 text-right">Actions</span>
                </div>

                {isLoading ? (
                    <div className="py-20 text-center text-slate-400 text-sm">Loading&hellip;</div>
                ) : data?.data.length === 0 ? (
                    <div className="py-20 text-center text-slate-400 text-sm">No users found.</div>
                ) : (
                    <div className="divide-y divide-slate-100">
                        {data?.data.map((user) => {
                            const plan = PLAN_CONFIG[user.plan ?? "free"] ?? PLAN_CONFIG.free;
                            const role = ROLE_CONFIG[user.role ?? "user"] ?? ROLE_CONFIG.user;

                            return (
                                <div key={user.id} className="hover:bg-slate-50 transition-colors">
                                    {/* Mobile layout */}
                                    <div className="flex md:hidden items-start gap-3 px-4 py-4">
                                        <div className="w-10 h-10 bg-gradient-to-br from-indigo-400 to-blue-500 rounded-full flex items-center justify-center flex-shrink-0 shadow-sm">
                                            <span className="text-sm font-bold text-white">
                                                {user.name.charAt(0).toUpperCase()}
                                            </span>
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <div className="flex items-start justify-between gap-2">
                                                <div className="min-w-0">
                                                    <p className="text-sm font-semibold text-slate-900 truncate">
                                                        {user.name} {user.surname}
                                                    </p>
                                                    <p className="text-xs text-slate-400 truncate">{user.email}</p>
                                                </div>
                                                <div className="flex items-center gap-1 flex-shrink-0">
                                                    <Button
                                                        variant="ghost"
                                                        size="icon-sm"
                                                        onClick={() => openEdit(user)}
                                                        title="Edit user"
                                                    >
                                                        <Pencil className="w-4 h-4 text-slate-500" />
                                                    </Button>
                                                    <Button
                                                        variant="ghost"
                                                        size="icon-sm"
                                                        onClick={() => openDelete(user)}
                                                        title="Delete user"
                                                    >
                                                        <Trash2 className="w-4 h-4 text-red-500" />
                                                    </Button>
                                                </div>
                                            </div>
                                            <div className="flex flex-wrap items-center gap-2 mt-2">
                                                {user.email_verified ? (
                                                    <span className="inline-flex items-center gap-1 text-xs font-medium text-emerald-600">
                                                        <CheckCircle className="w-3.5 h-3.5" />
                                                        Verified
                                                    </span>
                                                ) : (
                                                    <span className="inline-flex items-center gap-1 text-xs font-medium text-rose-400">
                                                        <XCircle className="w-3.5 h-3.5" />
                                                        Unverified
                                                    </span>
                                                )}
                                                <Badge className={`text-xs font-semibold ${plan.className}`}>
                                                    {plan.label}
                                                </Badge>
                                                <Badge
                                                    variant="secondary"
                                                    className={`flex w-fit items-center gap-1 text-xs font-semibold ${role.className}`}
                                                >
                                                    {role.icon}
                                                    {role.label}
                                                </Badge>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Desktop layout */}
                                    <div className="hidden md:grid md:grid-cols-[2fr_1fr_1fr_1fr_auto] gap-4 items-center px-6 py-4">
                                        {/* User info */}
                                        <div className="flex items-center gap-3 min-w-0">
                                            <div className="w-9 h-9 bg-gradient-to-br from-indigo-400 to-blue-500 rounded-full flex items-center justify-center flex-shrink-0 shadow-sm">
                                                <span className="text-sm font-bold text-white">
                                                    {user.name.charAt(0).toUpperCase()}
                                                </span>
                                            </div>
                                            <div className="min-w-0">
                                                <p className="text-sm font-semibold text-slate-900 truncate">
                                                    {user.name} {user.surname}
                                                </p>
                                                <p className="text-xs text-slate-400 truncate">{user.email}</p>
                                            </div>
                                        </div>

                                        {/* Verified */}
                                        <div className="flex items-center gap-1.5">
                                            {user.email_verified ? (
                                                <>
                                                    <CheckCircle className="w-4 h-4 text-emerald-500 flex-shrink-0" />
                                                    <span className="text-xs font-medium text-emerald-600">Verified</span>
                                                </>
                                            ) : (
                                                <>
                                                    <XCircle className="w-4 h-4 text-rose-400 flex-shrink-0" />
                                                    <span className="text-xs font-medium text-rose-400">Unverified</span>
                                                </>
                                            )}
                                        </div>

                                        {/* Plan */}
                                        <div>
                                            <Badge className={`text-xs font-semibold ${plan.className}`}>
                                                {plan.label}
                                            </Badge>
                                        </div>

                                        {/* Role */}
                                        <div>
                                            <Badge
                                                variant="secondary"
                                                className={`flex w-fit items-center gap-1 text-xs font-semibold ${role.className}`}
                                            >
                                                {role.icon}
                                                {role.label}
                                            </Badge>
                                        </div>

                                        {/* Actions */}
                                        <div className="flex items-center justify-end gap-1 w-24">
                                            <Button
                                                variant="ghost"
                                                size="icon-sm"
                                                onClick={() => openEdit(user)}
                                                title="Edit user"
                                            >
                                                <Pencil className="w-4 h-4 text-slate-500" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="icon-sm"
                                                onClick={() => openDelete(user)}
                                                title="Delete user"
                                            >
                                                <Trash2 className="w-4 h-4 text-red-500" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                )}
            </div>

            {/* Pagination */}
            {data && data.meta.last_page > 1 && (
                <div className="flex items-center justify-between">
                    <p className="text-sm text-slate-500">
                        Showing page <span className="font-medium text-slate-700">{data.meta.current_page}</span> of{" "}
                        <span className="font-medium text-slate-700">{data.meta.last_page}</span>
                        {" "}&middot;{" "}
                        <span className="font-medium text-slate-700">{data.meta.total}</span> users total
                    </p>
                    <div className="flex gap-2">
                        <button
                            onClick={() => goToPage(Math.max(1, page - 1))}
                            disabled={page === 1}
                            className="flex items-center gap-1 px-3 py-1.5 text-sm font-medium rounded-lg border border-slate-200 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                        >
                            <ChevronLeft className="w-4 h-4" />
                            Prev
                        </button>
                        <button
                            onClick={() => goToPage(Math.min(data.meta.last_page, page + 1))}
                            disabled={page === data.meta.last_page}
                            className="flex items-center gap-1 px-3 py-1.5 text-sm font-medium rounded-lg border border-slate-200 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                        >
                            Next
                            <ChevronRight className="w-4 h-4" />
                        </button>
                    </div>
                </div>
            )}

            {/* Edit Dialog */}
            <EditUserDialog
                user={editingUser}
                form={form}
                errors={errors}
                isLoading={actionLoading}
                onFormChange={setForm}
                onSubmit={handleUpdate}
                onClose={closeEdit}
            />

            {/* Delete Dialog */}
            <DeleteUserDialog
                user={deletingUser}
                isLoading={actionLoading}
                onConfirm={handleDelete}
                onClose={closeDelete}
            />
        </div>
    );
};
