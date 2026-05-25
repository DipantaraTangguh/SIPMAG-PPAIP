/**
 * DpmCard.jsx
 * Card displaying the assigned Dosen Pembimbing Magang (DPM).
 * Shows a placeholder when no DPM has been assigned yet.
 *
 * @prop {object|null} dpm — { name: string, email: string } or null.
 */
import React from 'react';
import { UserCircle, Mail } from 'lucide-react';

export default function DpmCard({ dpm = null }) {
    const name = dpm?.name ?? '—';
    const email = dpm?.email ?? '—';

    return (
        <div className="flex items-center gap-5 rounded-xl border border-gray-200 bg-white p-6 border-l-4 border-l-primary">
            {/* Avatar placeholder */}
            <div className="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-full bg-gray-100">
                <UserCircle className="h-8 w-8 text-gray-400" />
            </div>

            {/* Info */}
            <div className="min-w-0 flex-1">
                <p className="mb-1 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                    Dosen Pembimbing Magang (DPM)
                </p>
                <p className="text-base font-semibold text-gray-900">{name}</p>
                <div className="mt-1 flex items-center gap-1.5 text-sm text-gray-500">
                    <Mail className="h-3.5 w-3.5 text-primary" />
                    <span>{email}</span>
                </div>
            </div>
        </div>
    );
}
