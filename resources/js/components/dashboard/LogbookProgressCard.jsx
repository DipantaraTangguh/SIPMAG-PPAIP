/**
 * LogbookProgressCard.jsx
 * Card displaying the student's logbook completion progress (0–6).
 *
 * @prop {number} approvedCount — Number of approved logbook entries (0–6).
 */
import React from 'react';
import { BookMarked } from 'lucide-react';

const REQUIRED = 6;

export default function LogbookProgressCard({ approvedCount = 0 }) {
    const clamped = Math.min(Math.max(approvedCount, 0), REQUIRED);
    const pct = (clamped / REQUIRED) * 100;
    const remaining = REQUIRED - clamped;

    return (
        <div className="rounded-xl border border-gray-200 bg-white p-6">
            {/* Header */}
            <div className="mb-1 flex items-center justify-between">
                <h3 className="text-base font-bold text-gray-900">
                    Progress Logbook
                </h3>
                <BookMarked className="h-5 w-5 text-primary" />
            </div>
            <p className="mb-5 text-xs text-gray-400">
                Lengkapi {REQUIRED} laporan wajib
            </p>

            {/* Large count */}
            <div className="mb-4 text-center">
                <span className="block text-5xl font-bold text-primary">
                    {clamped}
                </span>
                <span className="mt-1 block text-[10px] font-bold uppercase tracking-widest text-gray-400">
                    Laporan Selesai
                </span>
            </div>

            {/* Progress bar */}
            <div className="mb-3 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                <div
                    className="h-full rounded-full bg-primary transition-all duration-500"
                    style={{ width: `${pct}%` }}
                />
            </div>

            {/* Footer */}
            <p className="text-right text-xs text-gray-400">
                Sisa {remaining} laporan lagi
            </p>
        </div>
    );
}
