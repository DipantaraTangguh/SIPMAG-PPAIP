/**
 * GuestRoute.jsx
 * Route guard that redirects logged-in users away from guest pages (e.g. /login).
 */
import React from 'react';
import { Navigate } from 'react-router-dom';
import { useSimulation } from '../context/SimulationContext';

export default function GuestRoute({ children }) {
    const { isLoggedIn } = useSimulation();
    if (isLoggedIn) return <Navigate to="/dashboard" replace />;
    return children;
}
