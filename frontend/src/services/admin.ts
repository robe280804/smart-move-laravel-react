import { api } from "@/lib/api";
import { handleApiError } from "@/lib/handleApiError";
import type { Feedback } from "@/types/feedback";
import type { User } from "@/types/auth";

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
