import { api } from '../lib/api';
import { handleApiError } from '../lib/handleApiError';
import type { RegisterFormData, LoginFormData } from '../types/forms';
import type { AuthResponse, RefreshResponse } from '../types/auth';

export const register = async (data: RegisterFormData): Promise<AuthResponse> => {
    try {
        const response = await api.post<AuthResponse>('/auth/register', data);
        return response.data;
    } catch (error) {
        return handleApiError(error);
    }
};

export const login = async (data: LoginFormData): Promise<AuthResponse> => {
    try {
        const response = await api.post<AuthResponse>('/auth/login', data);
        return response.data;
    } catch (error) {
        return handleApiError(error);
    }
};

/**
 * Silently exchange the HttpOnly refresh-token cookie for a new access token + user.
 * Throws on failure (expired / missing cookie) so the caller can handle logout.
 */
export const refresh = async (): Promise<RefreshResponse> => {
    const response = await api.post<RefreshResponse>('/refresh-token');
    return response.data;
};
