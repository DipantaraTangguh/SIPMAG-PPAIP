/**
 * context/AuthContext.jsx
 * Thin wrapper — delegates to SimulationContext for auth state.
 * Kept for backward compatibility with any component importing useAuth.
 */
import React, { createContext, useContext } from 'react';
import { useSimulation } from './SimulationContext';

const AuthContext = createContext();

export function AuthProvider({ children }) {
    // AuthProvider is now just a passthrough — SimulationProvider handles auth
    return children;
}

export function useAuth() {
    const sim = useSimulation();
    return {
        user: sim.student ? { ...sim.student, role: 'mahasiswa' } : null,
        isAuthenticated: sim.isLoggedIn,
        login: sim.login,
        logout: sim.logout,
    };
}
