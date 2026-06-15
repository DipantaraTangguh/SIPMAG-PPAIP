import React from 'react';

export default function IndependentSectionHeader({ count = 0 }) {
    return (
        <div className="flex items-center justify-between mt-8 mb-4">
            <div className="flex items-center gap-2.5">
                <h2 className="text-[18px] font-bold text-[#1A1A1A]">
                    Pengajuan Form 2 Saya
                </h2>
                <div className="rounded-full bg-gray-200 px-2 py-0.5 text-[12px] font-bold text-gray-600">
                    {count}
                </div>
            </div>
        </div>
    );
}
