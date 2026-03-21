import { api } from "@/lib/api";
import { handleApiError } from "@/lib/handleApiError";
import type { Feedback } from "@/types/feedback";
import type { User } from "@/types/auth";
import type { AdminUpdateUserFormData } from "@/components/forms/adminUser";

export interface PaginatedResponse<T> {
    data: T[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

export const getAdminUsers = async (page = 1, perPage = 15): Promise<PaginatedResponse<User>> => {
    try {
        const response = await api.get<PaginatedResponse<User>>(
            `/admin/users`,
            { params: { page, per_page: perPage } },
        );
        return response.data;
    } catch (error) {
        return handleApiError(error);
    }
};

export const updateAdminUser = async (userId: number, data: AdminUpdateUserFormData): Promise<User> => {
    try {
        const response = await api.put<{ data: User }>(`/admin/users/${userId}`, data);
        return response.data.data;
    } catch (error) {
        return handleApiError(error);
    }
};

export const deleteAdminUser = async (userId: number): Promise<void> => {
    try {
        await api.delete(`/admin/users/${userId}`);
    } catch (error) {
        return handleApiError(error);
    }
};

export const getAdminFeedbacks = async (page = 1, perPage = 15): Promise<PaginatedResponse<Feedback>> => {
    try {
        const response = await api.get<PaginatedResponse<Feedback>>(
            `/admin/feedbacks`,
            { params: { page, per_page: perPage } },
        );
        return response.data;
    } catch (error) {
        return handleApiError(error);
    }
};
