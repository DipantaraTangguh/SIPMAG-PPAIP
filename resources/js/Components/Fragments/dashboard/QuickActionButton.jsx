/**
 * QuickActionButton.jsx
 * Primary CTA card that adapts its label, style, and state based on access_status.
 *
 * @prop {string} accessStatus — One of: Unverified, PendingReview, RejectedForm1, ApprovedForm1, HasApplication, HasDPM, LogbookComplete.
 * @prop {function} onClick — Callback when the button is clicked.
 */
import React from 'react';
import { ChevronRight } from 'lucide-react';

const statusConfig = {
    Unverified: { label: 'Isi Form 1', disabled: false, style: 'primary' },
    PendingReview: { label: 'Form 1 Sedang Ditinjau', disabled: true, style: 'disabled' },
    RejectedForm1: { label: 'Revisi Form 1', disabled: false, style: 'primary' },
    ApprovedForm1: { label: 'Buka Portal Magang', disabled: false, style: 'primary' },
    HasApplication: { label: 'Ajukan Pembimbing Magang', disabled: false, style: 'primary' },
    HasDPM: { label: 'Isi Logbook Bimbingan', disabled: false, style: 'primary' },
    LogbookComplete: { label: 'Daftar Sidang Magang', disabled: false, style: 'primary' },
    MenungguSidang: { label: 'Menunggu Jadwal Sidang', disabled: true, style: 'disabled' },
    SiklusSelesai: { label: 'Siklus Magang Selesai', disabled: false, style: 'success' },
};

const buttonStyles = {
    primary: 'bg-primary text-white shadow-md hover:shadow-lg hover:brightness-110 active:brightness-95',
    success: 'bg-green-500 text-white shadow-md hover:bg-green-600 hover:shadow-lg active:brightness-95',
    disabled: 'cursor-not-allowed bg-gray-300 text-gray-500',
};

export default function QuickActionButton({
    accessStatus = 'Unverified',
    onClick,
}) {
    const config = statusConfig[accessStatus] ?? statusConfig.Unverified;
    const styleClass = buttonStyles[config.style] || buttonStyles.primary;

    return (
        <button
            type="button"
            onClick={config.disabled ? undefined : onClick}
            disabled={config.disabled}
            className={`
                flex w-full items-center justify-between rounded-xl px-7 py-6
                text-lg font-bold transition-all duration-200
                ${styleClass}
            `}
        >
            <span>{config.label}</span>
            <ChevronRight className="h-6 w-6" />
        </button>
    );
}
