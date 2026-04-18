/**
 * context/actions/authActions.js
 * Authentication actions for the simulation context.
 */
import { useCallback } from 'react';
import { INITIAL_STATE, STORAGE_KEY } from '../initialState';

export function useAuthActions(setState) {
    const login = useCallback((nim) => {
        if (nim === '1234567890') {
            setState((p) => ({ ...p, isLoggedIn: true }));
            return { success: true };
        }
        return { success: false, error: 'NIM atau kata sandi salah' };
    }, [setState]);

    const logout = useCallback(() => {
        localStorage.removeItem(STORAGE_KEY);
        setState(INITIAL_STATE);
    }, [setState]);

    return { login, logout };
}
