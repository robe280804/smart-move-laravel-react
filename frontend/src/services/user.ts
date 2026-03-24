import { api } from "../lib/api"
import type { User } from "../types/auth";
import { handleApiError } from "../lib/handleApiError";
import type { FitnessInfo, StoreFitnessInfoData, UpdateFitnessInfoData } from "@/types/user";
import type { ChangePasswordFormData } from "@/types/forms";

export const me = async (): Promise<User> => {
    try {
        const response = await api.get<User>("/user");
        return response.data;
    } catch (error) {
        return handleApiError(error);
    }
}

export const updatePersonalInfo = async (id: number, data: { name: string; surname: string; email: string }): Promise<User> => {
    try {
        const response = await api.put<{ data: User }>(`/users/${id}`, data);
        return response.data.data;
    } catch (error) {
        return handleApiError(error);
    }
}

export const getFitnessInfo = async (): Promise<FitnessInfo | null> => {
    try {
        const response = await api.get<{ data: FitnessInfo }>('/fitness-info');
        const fitness = response.data?.data;
        return fitness?.id ? fitness : null;
    } catch (error) {
        const axiosError = error as import('axios').AxiosError;
        if (axiosError.response?.status === 404) return null;
        return handleApiError(error);
    }
}


export const storeFitnessInfo = async (data: StoreFitnessInfoData): Promise<FitnessInfo> => {
    try {
        const response = await api.post<{ data: FitnessInfo }>('/fitness-info', data);
        return response.data.data;
    } catch (error) {
        return handleApiError(error);
    }
}

export const updateFitnessInfo = async (id: number, data: UpdateFitnessInfoData): Promise<FitnessInfo> => {
    try {
        const response = await api.put<{ data: FitnessInfo }>(`/fitness-info/${id}`, data);
        return response.data.data;
    } catch (error) {
        return handleApiError(error);
    }
}

export const changePassword = async (data: ChangePasswordFormData): Promise<void> => {
    try {
        await api.post('/users/change-password', data);
    } catch (error) {
        return handleApiError(error);
    }
}

export const deleteAccount = async (id: number): Promise<void> => {
    try {
        await api.delete(`/users/${id}`);
    } catch (error) {
        return handleApiError(error);
    }
}

export const exportUserData = async (id: number): Promise<Blob> => {
    try {
        const response = await api.get(`/users/${id}/export`, { responseType: "blob" });
        return response.data as Blob;
    } catch (error) {
        return handleApiError(error);
    }
}