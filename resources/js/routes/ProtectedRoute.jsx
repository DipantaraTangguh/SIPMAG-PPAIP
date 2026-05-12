/**
 * ProtectedRoute.jsx
 * Route guard that checks auth state.
 * Shows loading spinner during initial boot, redirects if not logged in.
 */
import React from 'react';
import { Navigate } from 'react-router-dom';
import { useSimulation } from '../context/SimulationContext';
import { FullScreenSpinner } from '../components/LoadingSpinner';

export default function ProtectedRoute({ children }) {
    const { isLoggedIn, isLoading } = useSimulation();
    if (isLoading) return <FullScreenSpinner />;
    if (!isLoggedIn) return <Navigate to="/login" replace />;
    return children;
}
