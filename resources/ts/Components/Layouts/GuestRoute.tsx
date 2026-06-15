import React from 'react';
import { Navigate } from 'react-router-dom';
import { useAuth } from '../../context/AppContext';
import { FullScreenSpinner } from '../Elements/LoadingSpinner';

export default function GuestRoute({ children }) {
    const { isLoggedIn, isLoading } = useAuth();
    if (isLoading) return <FullScreenSpinner />;
    if (isLoggedIn) return <Navigate to="/dashboard" replace />;
    return children;
}
