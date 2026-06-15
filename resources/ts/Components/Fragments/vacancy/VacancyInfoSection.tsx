import React from 'react';
import { Info } from 'lucide-react';

function InfoItem({ label, value }) {
    return (
        <div>
            <p className="text-[11px] font-bold uppercase tracking-wider text-gray-400">
                {label}
            </p>
            <p className="mt-1 text-[15px] font-bold text-gray-900">
                {value}
            </p>
        </div>
    );
}

export default function VacancyInfoSection({ vacancy }) {
    return (
        <div className="rounded-xl border border-gray-200 border-l-4 border-l-primary bg-white p-6">
            <div className="flex items-center gap-2">
                <Info className="h-[18px] w-[18px] text-primary" />
                <h3 className="text-base font-bold text-gray-900">
                    Informasi Lowongan
                </h3>
            </div>

            <hr className="my-4 border-gray-100" />
            <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <InfoItem label="Kapasitas" value={vacancy.kapasitas} />
                <InfoItem label="Sistem Kerja" value={vacancy.sistemKerja} />
                <InfoItem label="Durasi" value={vacancy.durasi} />
                <InfoItem label="Bidang" value={vacancy.bidang} />
            </div>
            <div className="mt-4 flex items-center gap-1.5 text-[13px] text-gray-500">
                <span>Mulai Magang:</span>
                <span className="font-bold text-gray-900">
                    {vacancy.mulaiMagang}
                </span>
            </div>
        </div>
    );
}
