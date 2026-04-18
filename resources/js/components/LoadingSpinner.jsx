/**
 * LoadingSpinner.jsx
 * Full-screen overlay spinner for simulated API delays,
 * plus an inline variant for button loading states.
 */
import React from 'react';

/** Full-screen overlay with centered spinner */
export function FullScreenSpinner() {
    return (
        <div className="fixed inset-0 z-[9998] flex flex-col items-center justify-center bg-white/80 backdrop-blur-sm">
            <div className="h-10 w-10 animate-spin rounded-full border-4 border-gray-200 border-t-primary" />
            <p className="mt-4 text-sm text-gray-500">Memuat...</p>
        </div>
    );
}

/** Small inline spinner (for buttons, white color) */
export function InlineSpinner({ className = 'h-[18px] w-[18px]' }) {
    return (
        <svg
            className={`animate-spin ${className}`}
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
        </svg>
    );
}
