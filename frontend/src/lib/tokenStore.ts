/**
 * In-memory access token store.
 *
 * The token is intentionally NOT persisted to localStorage or sessionStorage.
 * It lives only in the module closure for the duration of the browser session.
 * On page refresh the token is re-acquired via the HttpOnly refresh-token cookie
 * through the POST /refresh-token endpoint.
 */
type SessionUpdatedCallback = (token: string, expiresAt: string) => void;

let _accessToken: string | null = null;
let _onSessionUpdated: SessionUpdatedCallback | null = null;
let _loggedOut = false;

export const tokenStore = {
    get: (): string | null => _accessToken,
    set: (token: string | null): void => { _accessToken = token; },
    clear: (): void => { _accessToken = null; _loggedOut = true; },
    /** Call this only on an explicit login — resets the logged-out guard. */
    markActive: (): void => { _loggedOut = false; },
    isLoggedOut: (): boolean => _loggedOut,
    onSessionUpdated: (cb: SessionUpdatedCallback): void => { _onSessionUpdated = cb; },
    notifySessionUpdated: (token: string, expiresAt: string): void => {
        if (!_loggedOut) _onSessionUpdated?.(token, expiresAt);
    },
};
