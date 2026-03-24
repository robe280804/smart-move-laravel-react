import { useEffect } from "react";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { CreditCard, Dumbbell, Shield, User } from "lucide-react";
import { useSearchParams } from "react-router";
import { useAuth } from "@/contexts/AuthContext";
import { useProfileForm } from "@/hooks/useProfileForm";
import { useFitnessForm } from "@/hooks/useFitnessForm";
import { useSubscription } from "@/hooks/useSubscription";
import { ProfileHeroBanner } from "@/components/dashboard/profile/ProfileHeroBanner";
import { PersonalInfoTab } from "@/components/dashboard/profile/PersonalInfoTab";
import { FitnessProfileTab } from "@/components/dashboard/profile/FitnessProfileTab";
import { SubscriptionTab } from "@/components/dashboard/profile/SubscriptionTab";
import { SecurityTab } from "@/components/dashboard/profile/SecurityTab";
import { useSecurityForm } from "@/hooks/useSecurityForm";

export function ProfileAndSettings() {
    const { user, logout } = useAuth();
    const { form: profileForm, setForm: setProfileForm, errors: profileErrors, isLoading: isProfileLoading, handleSubmit: handleProfileSubmit } = useProfileForm();
    const { fitnessInfo, form: fitnessForm, setForm: setFitnessForm, errors: fitnessErrors, isLoading: isFitnessLoading, handleSubmit: handleFitnessSubmit } = useFitnessForm();
    const { currentPlan, isPlanLoading, checkoutLoadingPlan, handleSelectPlan, handleManageBilling, refetchPlan } = useSubscription();
    const { form: securityForm, setForm: setSecurityForm, errors: securityErrors, isLoading: isSecurityLoading, handleSubmit: handleSecuritySubmit } = useSecurityForm();
    const [searchParams] = useSearchParams();
    const initialTab = searchParams.get("tab") ?? "personal";

    useEffect(() => {
        if (searchParams.get("checkout") === "success") {
            refetchPlan();
        }
    }, []);

    return (
        <div className="space-y-6">
            <ProfileHeroBanner user={user} fitnessInfo={fitnessInfo} onLogout={logout} />

            <Tabs defaultValue={initialTab}>
                <TabsList className="grid grid-cols-4 w-full">
                    <TabsTrigger value="personal" className="flex items-center gap-1.5">
                        <User className="w-4 h-4 flex-shrink-0" />
                        <span className="hidden sm:inline">Personal</span>
                    </TabsTrigger>
                    <TabsTrigger value="fitness" className="flex items-center gap-1.5">
                        <Dumbbell className="w-4 h-4 flex-shrink-0" />
                        <span className="hidden sm:inline">Fitness</span>
                    </TabsTrigger>
                    <TabsTrigger value="subscription" className="flex items-center gap-1.5">
                        <CreditCard className="w-4 h-4 flex-shrink-0" />
                        <span className="hidden sm:inline">Plan</span>
                    </TabsTrigger>
                    <TabsTrigger value="security" className="flex items-center gap-1.5">
                        <Shield className="w-4 h-4 flex-shrink-0" />
                        <span className="hidden sm:inline">Security</span>
                    </TabsTrigger>
                </TabsList>

                <TabsContent value="personal" className="mt-4">
                    <PersonalInfoTab
                        form={profileForm}
                        errors={profileErrors}
                        isLoading={isProfileLoading}
                        onFormChange={setProfileForm}
                        onSubmit={handleProfileSubmit}
                    />
                </TabsContent>

                <TabsContent value="fitness" className="mt-4">
                    <FitnessProfileTab
                        fitnessInfo={fitnessInfo}
                        form={fitnessForm}
                        errors={fitnessErrors}
                        isLoading={isFitnessLoading}
                        onFormChange={setFitnessForm}
                        onSubmit={handleFitnessSubmit}
                    />
                </TabsContent>

                <TabsContent value="subscription" className="mt-4 space-y-4">
                    <SubscriptionTab
                        currentPlan={currentPlan}
                        isPlanLoading={isPlanLoading}
                        checkoutLoadingPlan={checkoutLoadingPlan}
                        onSelectPlan={handleSelectPlan}
                        onManageBilling={handleManageBilling}
                    />
                </TabsContent>

                <TabsContent value="security" className="mt-4 space-y-4">
                    <SecurityTab
                        form={securityForm}
                        errors={securityErrors}
                        isLoading={isSecurityLoading}
                        onFormChange={setSecurityForm}
                        onSubmit={handleSecuritySubmit}
                    />
                </TabsContent>
            </Tabs>
        </div>
    );
}
