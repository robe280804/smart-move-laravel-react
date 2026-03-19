export interface Feedback {
    id: number;
    user_id: number;
    user?: {
        name: string;
        surname: string;
        email: string;
    };
    rating: number | null;
    message: string | null;
    created_at: string;
    updated_at: string;
}

export interface StoreFeedbackData {
    rating: number | null;
    message: string | null;
}
