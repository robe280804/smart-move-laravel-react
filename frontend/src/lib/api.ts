import axios from 'axios';

export const api = axios.create({
    baseURL: import.meta.env.BASE_URL,
    headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
    },
});

api.interceptors.request.use((config) => {
    try {
        const raw = localStorage.getItem('smm:auth');
        if (raw) {
            const session = JSON.parse(raw);
            const token: string | undefined = session?.tokens?.access_token;
            if (token) config.headers.Authorization = `Bearer ${token}`;
        }
    } catch {
        // Malformed storage — skip auth header.
    }
    return config;
});
