export type UserRole = "user" | "admin";

export interface User {
    id: number;
    name: string;
    surname: string;
    email: string;
    role: UserRole | null;
    email_verified?: boolean;
    plan?: "free" | "advanced" | "pro";
}

export interface AccessToken {
    token: string;
    expires_at: string;
}

export interface AuthContextValue {
    user: User | null;
    accessToken: AccessToken | null;
    isAuthenticated: boolean;
    isAdmin: boolean;
    isLoading: boolean;
    setSession: (response: AuthResponse) => void;
    updateUser: (user: User) => void;
    logout: () => Promise<void>;
}

// Shape returned by POST /auth/login and POST /auth/register
export interface AuthResponse {
    data: {
        user: User;
    };
    meta_data: {
        accessToken: string;
        accessTokenExpiresAt: string;
    };
}

// Shape returned by POST /refresh-token (same structure)
export type RefreshResponse = AuthResponse;
