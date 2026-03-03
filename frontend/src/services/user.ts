import { api } from "../lib/api"
import type { User } from "../types/auth";
import { handleApiError } from "../lib/handleApiError";

export const me = async (): Promise<User> => {
    try {
        const response = await api.get<User>("/user");
        return response.data;
    } catch (error) {
        return handleApiError(error);
    }
}
