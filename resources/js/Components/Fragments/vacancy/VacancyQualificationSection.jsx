/**
 * VacancyQualificationSection.jsx
 * "Kualifikasi & Persyaratan" card with education, skills, and requirements.
 *
 * @prop {object} vacancy — Vacancy detail with pendidikanMinimal, keahlianUtama, persyaratan.
 */
import React from 'react';
import { CheckSquare, CheckCircle } from 'lucide-react';

export default function VacancyQualificationSection({ vacancy }) {
    return (
        <div className="rounded-xl border border-gray-200 border-l-4 border-l-primary bg-white p-6">
            {/* Header */}
            <div className="flex items-center gap-2">
                <CheckSquare className="h-[18px] w-[18px] text-primary" />
                <h3 className="text-base font-bold text-gray-900">
                    Kualifikasi &amp; Persyaratan
                </h3>
            </div>

            <hr className="my-4 border-gray-100" />

            {/* Pendidikan Minimal */}
            <p className="text-[11px] font-bold uppercase tracking-wider text-primary">
                Pendidikan Minimal
            </p>
            <p className="mt-2 text-sm font-bold leading-relaxed text-gray-900">
                {vacancy.pendidikanMinimal}
            </p>

            <hr className="my-4 border-gray-100" />

            {/* Keahlian Utama */}
            <p className="mb-2.5 text-[11px] font-bold uppercase tracking-wider text-primary">
                Keahlian Utama
            </p>
            <div className="flex flex-wrap gap-2">
                {vacancy.keahlianUtama.map((skill) => (
                    <span
                        key={skill}
                        className="rounded-full border border-gray-200 bg-white px-3.5 py-1.5 text-xs font-bold text-gray-700 transition-colors hover:border-primary hover:text-primary"
                    >
                        {skill}
                    </span>
                ))}
            </div>

            <hr className="my-4 border-gray-100" />

            {/* Persyaratan checklist */}
            <ul className="flex flex-col gap-2.5">
                {vacancy.persyaratan.map((item, idx) => (
                    <li key={idx} className="flex gap-2.5">
                        <CheckCircle className="mt-0.5 h-[18px] w-[18px] flex-shrink-0 text-primary" />
                        <span className="text-sm leading-relaxed text-gray-900">
                            {item}
                        </span>
                    </li>
                ))}
            </ul>
        </div>
    );
}
