/**
 * VacancyJobDescSection.jsx
 * "Deskripsi Pekerjaan" card with bullet-point list.
 *
 * @prop {string[]} deskripsiPekerjaan — Array of job description strings.
 */
import React from 'react';
import { FileText } from 'lucide-react';

export default function VacancyJobDescSection({ deskripsiPekerjaan }) {
    return (
        <div className="rounded-xl border border-gray-200 border-l-4 border-l-primary bg-white p-6">
            {/* Header */}
            <div className="flex items-center gap-2">
                <FileText className="h-[18px] w-[18px] text-primary" />
                <h3 className="text-base font-bold text-gray-900">
                    Deskripsi Pekerjaan
                </h3>
            </div>

            <hr className="my-4 border-gray-100" />

            {/* Bullet list */}
            <ul className="flex flex-col gap-3">
                {deskripsiPekerjaan.map((item, idx) => (
                    <li key={idx} className="flex gap-3">
                        <span className="mt-[7px] h-1.5 w-1.5 flex-shrink-0 rounded-full bg-primary" />
                        <span className="text-sm leading-relaxed text-gray-900">
                            {item}
                        </span>
                    </li>
                ))}
            </ul>
        </div>
    );
}
