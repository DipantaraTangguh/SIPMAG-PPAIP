import React from 'react';
import { CalendarCheck } from 'lucide-react';
import { useSimulation } from '../../../context/SimulationContext';

export default function DefenseScheduledView() {
    const { sidangSchedule } = useSimulation();

    // Safety net kalau context kosong di luar flow normal.
    const schedule = sidangSchedule || {
        tanggal: 'Belum ditetapkan',
        waktu: '-',
        ruangan: '-',
        dosenPenguji1: '-',
        dosenPenguji2: '-',
    };

    return (
        <div className="animate-in fade-in duration-500">
            <p className="mb-6 text-[14px] text-gray-500">
                Pengajuan verifikasi dokumen akhir dan jadwal sidang.
            </p>
            <div className="mx-auto max-w-[900px] rounded-2xl border border-gray-200 bg-white p-6 sm:p-[48px]">
                <div className="mx-auto w-[80px] h-[80px] rounded-full bg-primary-pale flex items-center justify-center">
                    <CalendarCheck className="w-[40px] h-[40px] text-primary" />
                </div>
                <h2 className="mt-[20px] text-center text-2xl font-bold leading-tight text-[#1A1A1A] sm:text-[28px]">
                    Jadwal Sidang Magang Anda
                </h2>
                <p className="text-center mt-[8px] text-gray-500 text-[14px] leading-relaxed max-w-[480px] mx-auto">
                    Berikut adalah detail jadwal pelaksanaan sidang magang Anda yang telah ditetapkan oleh Kepala Program Studi.
                </p>
                <div className="mt-[40px] w-full">
                    <h4 className="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-[16px]">
                        DETAIL PELAKSANAAN
                    </h4>
                    <div className="bg-primary-pale rounded-xl p-0 overflow-hidden">
                        {[
                            { label: 'Tanggal', value: schedule.tanggal },
                            { label: 'Waktu', value: schedule.waktu },
                            { label: 'Ruangan / Link', value: schedule.ruangan },
                            { label: 'Dosen Penguji 1', value: schedule.dosenPenguji1 },
                            { label: 'Dosen Penguji 2', value: schedule.dosenPenguji2 }
                        ].map((row, idx, arr) => (
                            <div 
                                key={idx} 
                                className={`flex flex-col gap-1 px-5 py-[18px] sm:flex-row sm:items-center sm:justify-between sm:px-[24px] ${idx !== arr.length - 1 ? 'border-b border-primary/10' : ''}`}
                            >
                                <span className="text-gray-500 text-[14px] font-normal">{row.label}</span>
                                <span className="font-bold text-[15px] text-[#1A1A1A] sm:max-w-[60%] sm:text-right leading-snug">
                                    {row.value}
                                </span>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
}
