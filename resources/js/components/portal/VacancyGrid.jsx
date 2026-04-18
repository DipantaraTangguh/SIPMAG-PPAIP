/**
 * VacancyGrid.jsx
 * 2-column grid listing of vacancy cards with results header + empty state.
 *
 * @prop {Array}    vacancies    — Filtered vacancy list.
 * @prop {string}   accessStatus — Student access status.
 * @prop {function} onCardClick  — Callback with vacancy id.
 */
import React from 'react';
import { Search } from 'lucide-react';
import VacancyCard from './VacancyCard';

export default function VacancyGrid({ vacancies, accessStatus, onCardClick }) {
    return (
        <div>
            {/* Header row */}
            <div className="mb-4 flex items-baseline justify-between">
                <h3 className="text-base font-bold text-gray-900">
                    Lowongan Tersedia
                </h3>
                <span className="text-xs font-bold uppercase tracking-wider text-primary">
                    {vacancies.length} Hasil Ditemukan
                </span>
            </div>

            {/* Grid or empty state */}
            {vacancies.length > 0 ? (
                <div className="grid grid-cols-2 gap-4">
                    {vacancies.map((v) => (
                        <VacancyCard
                            key={v.id}
                            vacancy={v}
                            onCardClick={onCardClick}
                        />
                    ))}
                </div>
            ) : (
                <div className="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-200 py-20">
                    <Search className="h-12 w-12 text-gray-300" />
                    <p className="mt-4 text-base font-bold text-gray-400">
                        Tidak ada lowongan ditemukan
                    </p>
                    <p className="mt-1 text-[13px] text-gray-400">
                        Coba kata kunci yang berbeda
                    </p>
                </div>
            )}
        </div>
    );
}
