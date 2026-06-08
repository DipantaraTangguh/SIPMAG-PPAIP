import React, {
    createContext,
    useCallback,
    useContext,
    useEffect,
    useMemo,
    useState,
} from 'react';
import { api } from '../lib/api';
import { mapStudent } from './simulationMappers';

const AuthContext = createContext(null);

const EMPTY_AUTH_STATE = {
    isLoggedIn: false,
    isLoading: true,
    student: null,
    userRole: null,
};

export function AuthProvider({ children }) {
    const [auth, setAuth] = useState(EMPTY_AUTH_STATE);

    const refreshProfile = useCallback(async () => {
        try {
            const { user } = await api.get('/me');
            setAuth((s) => ({
                ...s,
                isLoggedIn: true,
                isLoading: false,
                student: mapStudent(user),
                userRole: user.role,
            }));
        } catch {
            setAuth((s) => ({ ...s, isLoading: false }));
        }
    }, []);

    useEffect(() => {
        refreshProfile();
    }, [refreshProfile]);

    const login = useCallback(async (loginId, password) => {
        try {
            const data = await api.login(loginId, password);
            setAuth({
                isLoggedIn: true,
                isLoading: false,
                student: mapStudent(data.user),
                userRole: data.user.role,
            });

            return { success: true };
        } catch (err) {
            return { success: false, error: err.message };
        }
    }, []);

    const logout = useCallback(async () => {
        await api.logout();
        setAuth({ ...EMPTY_AUTH_STATE, isLoading: false });
    }, []);

    const updateStudentLocally = useCallback((patch) => {
        setAuth((s) => ({
            ...s,
            student: s.student ? { ...s.student, ...patch } : s.student,
        }));
    }, []);

    const value = useMemo(() => ({
        ...auth,
        login,
        logout,
        refreshProfile,
        updateStudentLocally,
        user: auth.student ? { ...auth.student, role: auth.userRole } : null,
        isAuthenticated: auth.isLoggedIn,
    }), [auth, login, logout, refreshProfile, updateStudentLocally]);

    return (
        <AuthContext.Provider value={value}>
            {children}
        </AuthContext.Provider>
    );
}

export function useAuth() {
    const ctx = useContext(AuthContext);
    if (!ctx) throw new Error('useAuth must be used within AuthProvider');
    return ctx;
}
