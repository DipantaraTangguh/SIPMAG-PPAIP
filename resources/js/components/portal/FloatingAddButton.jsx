/**
 * FloatingAddButton.jsx
 * Floating action button (bottom-right) for adding mandiri internship.
 *
 * @prop {string}   accessStatus — Student access status.
 * @prop {function} onClick      — Callback for button click.
 */
import React, { useState } from 'react';
import { Plus } from 'lucide-react';
import { canAccessPortal } from '../../utils/accessUtils';

export default function FloatingAddButton({ accessStatus, onClick }) {
    const [showTooltip, setShowTooltip] = useState(false);
    const isApproved = canAccessPortal(accessStatus);

    const handleClick = () => {
        if (isApproved) {
            onClick?.();
        } else {
            setShowTooltip(true);
            setTimeout(() => setShowTooltip(false), 2500);
        }
    };

    return (
        <div className="fixed bottom-6 right-6 z-50">
            {/* Tooltip */}
            {showTooltip && (
                <div className="absolute -top-12 right-0 whitespace-nowrap rounded-lg bg-gray-900 px-3 py-2 text-xs font-medium text-white shadow-lg">
                    Selesaikan Form 1 terlebih dahulu
                    <div className="absolute -bottom-1 right-5 h-2 w-2 rotate-45 bg-gray-900" />
                </div>
            )}

            {/* FAB */}
            <button
                type="button"
                onClick={handleClick}
                className="flex h-14 w-14 items-center justify-center rounded-full bg-primary text-white shadow-lg transition-all hover:scale-105 hover:bg-primary-hover"
            >
                <Plus className="h-6 w-6" />
            </button>
        </div>
    );
}
