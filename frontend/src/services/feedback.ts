import { api } from "@/lib/api";
import { handleApiError } from "@/lib/handleApiError";
import type { Feedback, StoreFeedbackData } from "@/types/feedback";

export const storeFeedback = async (data: StoreFeedbackData): Promise<Feedback> => {
    try {
        const response = await api.post<{ data: Feedback }>("/feedbacks", data);
        return response.data.data;
    } catch (error) {
        return handleApiError(error);
    }
};
