import axios from 'axios';
import { tokenStore } from './tokenStore';
import type { RefreshResponse } from '../types/auth';

export const api = axios.create({
    baseURL: import.meta.env.VITE_BACKEND_BASE_URL,
    withCredentials: true, // required to send the HttpOnly refresh-token cookie
    headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
    },
});

// Separate instance for the refresh call so it never triggers the interceptor below
const refreshApi = axios.create({
    baseURL: import.meta.env.VITE_BACKEND_BASE_URL,
    withCredentials: true,
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
});

api.interceptors.request.use((config) => {
    const token = tokenStore.get();
    if (token) config.headers.Authorization = `Bearer ${token}`;
    return config;
});

let isRefreshing = false;
let pendingQueue: Array<(token: string) => void> = [];

const processQueue = (token: string) => {
    pendingQueue.forEach((cb) => cb(token));
    pendingQueue = [];
};

api.interceptors.response.use(
    (response) => response,
    async (error) => {
        const original = error.config;

        if (error.response?.status !== 401 || original._retry || tokenStore.isLoggedOut() || original.url?.includes('refresh-token')) {
            return Promise.reject(error);
        }

        // If a refresh is already in flight, queue this request to retry once done
        if (isRefreshing) {
            return new Promise((resolve) => {
                pendingQueue.push((token) => {
                    original.headers.Authorization = `Bearer ${token}`;
                    resolve(api(original));
                });
            });
        }

        original._retry = true;
        isRefreshing = true;

        try {
            const { data } = await refreshApi.post<RefreshResponse>('/refresh-token');
            const newToken = data.meta_data.accessToken;
            const expiresAt = data.meta_data.accessTokenExpiresAt;

            tokenStore.set(newToken);
            tokenStore.notifySessionUpdated(newToken, expiresAt);
            processQueue(newToken);

            original.headers.Authorization = `Bearer ${newToken}`;
            return api(original);
        } catch (refreshError) {
            tokenStore.clear();
            pendingQueue = [];
            return Promise.reject(refreshError);
        } finally {
            isRefreshing = false;
        }
    }
);
