import React, { createContext, useContext, useState, useEffect } from 'react';
import { fetchWithCsrf } from '../lib/fetch';

const AuthContext = createContext();

/**
 * DEV_MOCK_USER — Used while the backend API isn't wired up yet.
 * Set to `null` to disable the mock and require real authentication.
 */
const DEV_MOCK_USER = {
    id: 1,
    name: 'Tangguh Dipantara',
    role: 'student',
    student: {
        id: 1,
        nim: '1234567890',
        name: 'Tangguh Dipantara',
        study_program: 'Informatika',
        access_status: 'Unverified',
        is_independent: false,
        approved_logbook_count: 0,
        dpm: null,
    },
};

export function AuthProvider({ children }) {
    const [user, setUser] = useState(null);
    const [isLoading, setIsLoading] = useState(true);

    const isAuthenticated = !!user;

    const fetchUser = async () => {
        try {
            const data = await fetchWithCsrf('/api/user');
            setUser(data);
        } catch (error) {
            // Fallback to mock user in development when API isn't available
            if (DEV_MOCK_USER) {
                console.warn('[AuthContext] API unavailable — using DEV_MOCK_USER');
                setUser(DEV_MOCK_USER);
            } else {
                setUser(null);
            }
        } finally {
            setIsLoading(false);
        }
    };

    const login = async (credentials) => {
        await fetchWithCsrf('/sanctum/csrf-cookie', { method: 'GET' });
        const response = await fetchWithCsrf('/api/login', {
            method: 'POST',
            body: JSON.stringify(credentials),
        });
        await fetchUser();
        return response;
    };

    const logout = async () => {
        try {
            await fetchWithCsrf('/api/logout', { method: 'POST' });
        } catch (e) {
            // ignore — API may not exist yet
        }
        setUser(null);
    };

    useEffect(() => {
        fetchUser();
    }, []);

    if (isLoading)
        return (
            <div className="flex h-screen items-center justify-center text-gray-400">
                Loading...
            </div>
        );

    return (
        <AuthContext.Provider value={{ user, isAuthenticated, login, logout }}>
            {children}
        </AuthContext.Provider>
    );
}

export const useAuth = () => useContext(AuthContext);
