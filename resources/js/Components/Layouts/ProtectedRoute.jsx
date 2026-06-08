import React, { useEffect } from 'react';
import { Navigate } from 'react-router-dom';
import { useAuth } from '../../context/AppContext';
import { FullScreenSpinner } from '../Elements/LoadingSpinner';

function ExternalRedirect({ to }) {
    useEffect(() => {
        window.location.replace(to);
    }, [to]);

    return <FullScreenSpinner />;
}

export default function ProtectedRoute({
    children,
    allowedRoles = [],
    unauthorizedTo = '/admin',
}) {
    const { isLoggedIn, isLoading, userRole } = useAuth();
    const roles = Array.isArray(allowedRoles) ? allowedRoles : [allowedRoles];

    if (isLoading) return <FullScreenSpinner />;
    if (! isLoggedIn) return <Navigate to="/login" replace />;
    if (roles.length > 0 && ! roles.includes(userRole)) {
        return <ExternalRedirect to={unauthorizedTo} />;
    }

    return children;
}
