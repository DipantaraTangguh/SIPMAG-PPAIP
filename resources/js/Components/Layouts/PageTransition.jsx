/**
 * PageTransition.jsx
 * Wraps page content with a fade-in + slide-up animation.
 */
import React from 'react';

export default function PageTransition({ children }) {
    return <div className="animate-fadeIn">{children}</div>;
}
