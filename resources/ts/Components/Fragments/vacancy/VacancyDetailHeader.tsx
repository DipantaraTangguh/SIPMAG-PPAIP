import React from 'react';

export default function VacancyDetailHeader({ vacancy }) {
    return (
        <div className="relative overflow-hidden rounded-xl border border-gray-200 bg-white p-6">
            <span
                className="pointer-events-none absolute -right-2 -top-4 select-none text-[140px] font-black leading-none text-gray-900/5"
                aria-hidden="true"
            >
                {vacancy.logoInitial}
            </span>
            <div className="relative flex items-center gap-5">
                {vacancy.logoUrl ? (
                    <img
                        src={vacancy.logoUrl}
                        alt={`Logo ${vacancy.companyName}`}
                        className="h-16 w-16 flex-shrink-0 rounded-xl border border-gray-100 bg-white object-contain p-1"
                    />
                ) : (
                    <div
                        className="flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-xl text-[28px] font-bold text-white"
                        style={{ backgroundColor: vacancy.logoColor }}
                    >
                        {vacancy.logoInitial}
                    </div>
                )}
                <div>
                    <h2 className="text-2xl font-bold text-gray-900">
                        {vacancy.position}
                    </h2>
                    <p className="mt-1 text-base text-primary">
                        {vacancy.companyName}
                    </p>
                </div>
            </div>
        </div>
    );
}
