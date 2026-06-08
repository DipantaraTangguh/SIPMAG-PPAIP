import React from 'react';
import { Navigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { FullScreenSpinner } from '../Elements/LoadingSpinner';

export default function ProtectedRoute({ children }) {
    const { isLoggedIn, isLoading } = useAuth();
    if (isLoading) return <FullScreenSpinner />;
    if (!isLoggedIn) return <Navigate to="/login" replace />;
    return children;
}
