import React from 'react';
import { MapPin, ArrowRight } from 'lucide-react';

// Dua chip cukup untuk hampir semua lowongan; sisanya diringkas supaya kartu
// tidak melar dan tingginya tetap seragam di dalam grid.
const CHIP_TAMPIL = 2;

export default function VacancyCard({ vacancy, onCardClick }) {
    const prodi = vacancy.studyPrograms ?? [];
    const prodiTampil = prodi.slice(0, CHIP_TAMPIL);
    const prodiSisa = prodi.length - prodiTampil.length;

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
            {/* Prodi yang boleh melamar dulu cuma terbaca di halaman detail,
                padahal itu penyaring pertama yang dilihat mahasiswa. Daftar
                kosong berarti terbuka untuk semua prodi -- sama seperti yang
                dipakai Internship::acceptsStudyProgram(). */}
            <div className="mt-3 flex flex-wrap items-center gap-1.5">
                {prodi.length === 0 ? (
                    <span className="rounded-md bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-500">
                        Semua program studi
                    </span>
                ) : (
                    <>
                        {prodiTampil.map((nama) => (
                            <span
                                key={nama}
                                className="rounded-md bg-primary-pale px-2 py-0.5 text-[11px] font-semibold text-primary"
                            >
                                {nama}
                            </span>
                        ))}
                        {prodiSisa > 0 && (
                            <span
                                title={prodi.join(', ')}
                                className="rounded-md bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-500"
                            >
                                +{prodiSisa} prodi
                            </span>
                        )}
                    </>
                )}
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
