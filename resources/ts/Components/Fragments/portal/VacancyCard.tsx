import React from 'react';
import { MapPin, ArrowRight } from 'lucide-react';

export default function VacancyCard({ vacancy, onCardClick }) {
    return (
        <div
            onClick={() => onCardClick(vacancy.id)}
            className="cursor-pointer rounded-xl border border-gray-200 border-l-4 border-l-primary bg-white p-5 transition-all duration-200 hover:-translate-y-1 hover:border-primary hover:bg-red-50 hover:shadow-md"
        >
            <div className="flex items-start justify-between">
                {vacancy.logoUrl ? (
                    <img
                        src={vacancy.logoUrl}
                        alt={`Logo ${vacancy.companyName}`}
                        className="h-10 w-10 rounded-lg border border-gray-100 bg-white object-contain"
                    />
                ) : (
                    <div
                        className="flex h-10 w-10 items-center justify-center rounded-lg text-base font-bold text-white"
                        style={{ backgroundColor: vacancy.logoColor }}
                    >
                        {vacancy.logoInitial}
                    </div>
                )}
                <span className="rounded-full bg-primary-pale px-2.5 py-1 text-xs font-bold uppercase text-primary">
                    {vacancy.deadline}
                </span>
            </div>
            <div className="mt-3">
                <p className="text-[15px] font-bold text-gray-900">
                    {vacancy.position}
                </p>
                <p className="mt-0.5 text-[13px] text-gray-500">
                    {vacancy.companyName}
                </p>
            </div>
            <div className="mt-4 flex items-center justify-between">
                <div className="flex items-center gap-1 text-xs text-gray-400">
                    <MapPin className="h-3 w-3" />
                    <span>{vacancy.location}</span>
                </div>
                <span className="flex items-center gap-1 text-[13px] font-bold text-primary hover:underline">
                    Lihat Detail
                    <ArrowRight className="h-3.5 w-3.5" />
                </span>
            </div>
        </div>
    );
}
