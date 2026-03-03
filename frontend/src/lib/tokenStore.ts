/**
 * In-memory access token store.
 *
 * The token is intentionally NOT persisted to localStorage or sessionStorage.
 * It lives only in the module closure for the duration of the browser session.
 * On page refresh the token is re-acquired via the HttpOnly refresh-token cookie
 * through the POST /refresh-token endpoint.
 */
let _accessToken: string | null = null;

export const tokenStore = {
    get: (): string | null => _accessToken,
    set: (token: string | null): void => { _accessToken = token; },
    clear: (): void => { _accessToken = null; },
};
