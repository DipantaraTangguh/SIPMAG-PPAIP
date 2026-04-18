/**
 * ProtectedRoute.jsx
 * Route guard that checks simulation login state.
 */
import React from 'react';
import { Navigate } from 'react-router-dom';
import { useSimulation } from '../context/SimulationContext';

export default function ProtectedRoute({ children }) {
    const { isLoggedIn } = useSimulation();
    if (!isLoggedIn) return <Navigate to="/login" replace />;
    return children;
}
