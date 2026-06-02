import React from 'react';
import { Navigate } from 'react-router-dom';
import { useSimulation } from '../../context/SimulationContext';
import { FullScreenSpinner } from '../Elements/LoadingSpinner';

export default function GuestRoute({ children }) {
    const { isLoggedIn, isLoading } = useSimulation();
    if (isLoading) return <FullScreenSpinner />;
    if (isLoggedIn) return <Navigate to="/dashboard" replace />;
    return children;
}
