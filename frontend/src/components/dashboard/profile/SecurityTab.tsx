import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";

export function SecurityTab() {
    return (
        <>
            <Card>
                <CardHeader>
                    <CardTitle>Change Password</CardTitle>
                    <CardDescription>Update your password to keep your account secure</CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                    <div className="space-y-2">
                        <Label htmlFor="currentPassword">Current Password</Label>
                        <Input id="currentPassword" type="password" placeholder="Enter current password" />
                    </div>
                    <div className="grid sm:grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="newPassword">New Password</Label>
                            <Input id="newPassword" type="password" placeholder="Enter new password" />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="confirmPassword">Confirm Password</Label>
                            <Input id="confirmPassword" type="password" placeholder="Repeat new password" />
                        </div>
                    </div>
                    <div className="pt-2">
                        <Button className="bg-gradient-to-r from-blue-600 to-indigo-600">
                            Update Password
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Account Security</CardTitle>
                    <CardDescription>Additional security options</CardDescription>
                </CardHeader>
                <CardContent className="space-y-3">
                    <div className="flex items-center justify-between p-4 border rounded-lg">
                        <div>
                            <p className="font-medium text-slate-900">Two-Factor Authentication</p>
                            <p className="text-sm text-slate-500">Add an extra layer of security to your account</p>
                        </div>
                        <Button variant="outline">Enable</Button>
                    </div>
                    <div className="flex items-center justify-between p-4 border rounded-lg border-red-200 bg-red-50">
                        <div>
                            <p className="font-medium text-red-900">Delete Account</p>
                            <p className="text-sm text-red-600">Permanently delete your account and all data</p>
                        </div>
                        <Button variant="outline" className="border-red-300 text-red-700 hover:bg-red-100">
                            Delete
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </>
    );
}
