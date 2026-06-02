import React, { createContext, useContext } from 'react';
import { useSimulation } from './SimulationContext';

const AuthContext = createContext();

export function AuthProvider({ children }) {
    // Auth lama tetap ada biar import nggak pecah, logic-nya sekarang di SimulationProvider.
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
