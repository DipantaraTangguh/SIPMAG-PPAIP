/**
 * TipsMagangCard.jsx
 * Card displaying a list of internship tips.
 *
 * @prop {string[]} tips — Array of tip strings to display.
 */
import React from 'react';
import { HelpCircle } from 'lucide-react';

const defaultTips = [
    'Jangan lupa isi logbook sebagai syarat untuk bisa melakukan sidang magang.',
    'Gunakan foto profil profesional untuk meningkatkan kepercayaan mitra magang.',
];

export default function TipsMagangCard({ tips = defaultTips }) {
    return (
        <div className="rounded-xl border border-gray-200 bg-white p-6">
            {/* Header */}
            <div className="mb-4 flex items-center gap-2">
                <HelpCircle className="h-5 w-5 text-primary" />
                <h3 className="text-base font-bold text-primary">
                    Tips Magang
                </h3>
            </div>

            {/* Tips list */}
            <ul className="flex flex-col gap-4">
                {tips.map((tip, idx) => (
                    <li key={idx} className="flex gap-3">
                        <span className="mt-1.5 h-2.5 w-2.5 flex-shrink-0 rounded-full bg-primary" />
                        <p className="text-sm leading-relaxed text-gray-500">
                            {tip}
                        </p>
                    </li>
                ))}
            </ul>
        </div>
    );
}
