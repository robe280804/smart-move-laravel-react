import { createContext, useState, useContext, type ReactNode, useEffect, useRef } from "react";
import type { User, AuthResponse, AuthContextValue, AccessToken } from "../types/auth";
import { tokenStore } from "../lib/tokenStore";
import { refresh, logoutUser } from "../services/authentication";
import { notify } from "../lib/toast";
import { clearAllGeneratingPlans } from "../hooks/useGeneratingPlans";

const AuthContext = createContext<AuthContextValue | null>(null);

export const AuthProvider = ({ children }: { children: ReactNode }) => {
    const [user, setUser] = useState<User | null>(null);
    const [accessToken, setAccessToken] = useState<AccessToken | null>(null);
    const [isLoading, setIsLoading] = useState(true);
    const expiryWarningTimer = useRef<ReturnType<typeof setTimeout> | null>(null);

    const isAuthenticated = !!accessToken && new Date(accessToken.expires_at) > new Date();
    const isAdmin = user?.role === "admin";

    // Warn the user 5 minutes before their session expires
    useEffect(() => {
        if (expiryWarningTimer.current) clearTimeout(expiryWarningTimer.current);

        if (!accessToken) return;

        const msUntilWarning = new Date(accessToken.expires_at).getTime() - Date.now() - 5 * 60 * 1000;

        if (msUntilWarning <= 0) return;

        expiryWarningTimer.current = setTimeout(() => {
            notify.warning("Your session expires in 5 minutes. Save your work.");
        }, msUntilWarning);

        return () => {
            if (expiryWarningTimer.current) clearTimeout(expiryWarningTimer.current);
        };
    }, [accessToken]);

    /**
     * On app start, silently exchange the HttpOnly refresh-token cookie for a
     * new access token. The browser sends the cookie automatically (withCredentials).
     * If the cookie is missing or expired, the user is simply left unauthenticated.
     */
    useEffect(() => {
        tokenStore.onSessionUpdated((token, expiresAt) => {
            setAccessToken({ token, expires_at: expiresAt });
        });
    }, []);

    useEffect(() => {
        let cancelled = false;

        const restoreSession = async () => {
            try {
                const response = await refresh();
                if (cancelled || tokenStore.isLoggedOut()) return;
                tokenStore.markActive();
                tokenStore.set(response.meta_data.accessToken);
                setUser(response.data.user);
                setAccessToken({
                    token: response.meta_data.accessToken,
                    expires_at: response.meta_data.accessTokenExpiresAt,
                });
            } catch {
                if (!cancelled) tokenStore.clear();
            } finally {
                if (!cancelled) setIsLoading(false);
            }
        };

        restoreSession();

        return () => { cancelled = true; };
    }, []);

    const setSession = (response: AuthResponse) => {
        tokenStore.markActive();
        tokenStore.set(response.meta_data.accessToken);
        setUser(response.data.user);
        setAccessToken({
            token: response.meta_data.accessToken,
            expires_at: response.meta_data.accessTokenExpiresAt,
        });
    };

    const updateUser = (updated: import("../types/auth").User) => {
        setUser(updated);
    };

    const logout = async () => {
        try {
            await logoutUser(); // invalidates the refresh token cookie server-side
        } finally {
            clearAllGeneratingPlans();
            sessionStorage.clear();
            tokenStore.clear();
            setAccessToken(null);
            setUser(null);
        }
    };

    return (
        <AuthContext.Provider
            value={{ user, accessToken, isAuthenticated, isAdmin, isLoading, setSession, updateUser, logout }}>
            {children}
        </AuthContext.Provider>
    );
};


export const useAuth = () => {
    const ctx = useContext(AuthContext);
    if (!ctx) throw new Error("useAuth must be used inside AuthProvider");
    return ctx;
};
