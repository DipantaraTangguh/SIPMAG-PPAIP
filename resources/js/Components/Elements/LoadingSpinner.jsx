import React from 'react';
import { Loader2 } from 'lucide-react';
export function FullScreenSpinner() {
    return (
        <div className="fixed inset-0 z-[9998] flex flex-col items-center justify-center bg-white/80 backdrop-blur-sm">
            <div className="h-10 w-10 animate-spin rounded-full border-4 border-gray-200 border-t-primary" />
            <p className="mt-4 text-sm text-gray-500">Memuat...</p>
        </div>
    );
}
export function InlineSpinner({ className = 'h-[18px] w-[18px]' }) {
    return (
        <Loader2 className={`animate-spin ${className}`} />
    );
}

