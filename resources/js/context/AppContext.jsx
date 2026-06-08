import React, { createContext, useContext, useMemo } from 'react';
import { AuthProvider, useAuth } from './AuthContext';
import {
    StudentWorkflowProvider,
    useStudentWorkflow,
} from './StudentWorkflowContext';

const AppContext = createContext(null);

function AppContextAdapter({ children }) {
    const auth = useAuth();
    const workflow = useStudentWorkflow();

    const value = useMemo(() => ({
        ...workflow,
        ...auth,
    }), [auth, workflow]);

    return (
        <AppContext.Provider value={value}>
            {children}
        </AppContext.Provider>
    );
}

export function AppProvider({ children }) {
    return (
        <AuthProvider>
            <StudentWorkflowProvider>
                <AppContextAdapter>{children}</AppContextAdapter>
            </StudentWorkflowProvider>
        </AuthProvider>
    );
}

export function useAppContext() {
    const ctx = useContext(AppContext);
    if (!ctx) throw new Error('useAppContext must be used within AppProvider');
    return ctx;
}
