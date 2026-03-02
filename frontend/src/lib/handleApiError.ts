import type { AxiosError } from 'axios';
import { ApiError } from './apiError';

interface ApiErrorResponse {
    message?: string;
    errors?: Record<string, string[]>;
}

export const handleApiError = (error: unknown): never => {
    const axiosError = error as AxiosError<ApiErrorResponse>;

    if (axiosError.response) {
        const { status, data } = axiosError.response;
        throw new ApiError(
            data?.message ?? 'Server error',
            status,
            data?.errors,
        );
    }

    if (axiosError.request) {
        throw new ApiError('No response from server. Please try again later.');
    }

    throw new ApiError(
        axiosError.message ?? 'Unknown error occurred',
    );
};
