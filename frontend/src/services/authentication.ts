import { api } from '../lib/api';
import { handleApiError } from '../lib/handleApiError';
import type { RegisterFormData, LoginFormData } from '../types/forms';
import type { AuthResponse } from '../types/auth';

export const register = async (data: RegisterFormData): Promise<AuthResponse> => {
    try {
        const response = await api.post<AuthResponse>('/users/register', data);
        return response.data;
    } catch (error) {
        return handleApiError(error);
    }
};

export const login = async (data: LoginFormData): Promise<AuthResponse> => {
    try {
        const response = await api.post<AuthResponse>('/users/login', data);
        return response.data;
    } catch (error) {
        return handleApiError(error);
    }
};
