import { Pencil } from "lucide-react";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import type { User } from "@/types/auth";
import type { AdminUpdateUserFormData, AdminUpdateUserFormErrors } from "@/components/forms/adminUser";

interface EditUserDialogProps {
    user: User | null;
    form: AdminUpdateUserFormData;
    errors: AdminUpdateUserFormErrors;
    isLoading: boolean;
    onFormChange: (form: AdminUpdateUserFormData) => void;
    onSubmit: (e: React.FormEvent) => void;
    onClose: () => void;
}

export const EditUserDialog = ({
    user,
    form,
    errors,
    isLoading,
    onFormChange,
    onSubmit,
    onClose,
}: EditUserDialogProps) => {
    return (
        <Dialog open={user !== null} onOpenChange={(open) => !open && onClose()}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2">
                        <Pencil className="w-4 h-4 text-indigo-600" />
                        Edit User
                    </DialogTitle>
                    <DialogDescription>
                        Update the details for {user?.name} {user?.surname}.
                    </DialogDescription>
                </DialogHeader>

                <form onSubmit={onSubmit} className="space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="edit-name">Name</Label>
                            <Input
                                id="edit-name"
                                value={form.name}
                                onChange={(e) => onFormChange({ ...form, name: e.target.value })}
                                aria-invalid={!!errors.name}
                            />
                            {errors.name && (
                                <p className="text-xs text-red-500">{errors.name}</p>
                            )}
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="edit-surname">Surname</Label>
                            <Input
                                id="edit-surname"
                                value={form.surname}
                                onChange={(e) => onFormChange({ ...form, surname: e.target.value })}
                                aria-invalid={!!errors.surname}
                            />
                            {errors.surname && (
                                <p className="text-xs text-red-500">{errors.surname}</p>
                            )}
                        </div>
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="edit-email">Email</Label>
                        <Input
                            id="edit-email"
                            type="email"
                            value={form.email}
                            onChange={(e) => onFormChange({ ...form, email: e.target.value })}
                            aria-invalid={!!errors.email}
                        />
                        {errors.email && (
                            <p className="text-xs text-red-500">{errors.email}</p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="edit-role">Role</Label>
                        <select
                            id="edit-role"
                            value={form.role}
                            onChange={(e) => onFormChange({ ...form, role: e.target.value as "user" | "admin" })}
                            className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] outline-none"
                        >
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                        {errors.role && (
                            <p className="text-xs text-red-500">{errors.role}</p>
                        )}
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={onClose} disabled={isLoading}>
                            Cancel
                        </Button>
                        <Button type="submit" disabled={isLoading}>
                            {isLoading ? "Saving..." : "Save Changes"}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
};
