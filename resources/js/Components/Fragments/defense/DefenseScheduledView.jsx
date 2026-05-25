/**
 * components/defense/DefenseScheduledView.jsx
 * State 3: Schedule detail card + Persiapan Sidang panel.
 * Extracted from InternshipDefensePage.jsx lines 313–400.
 */
import React from 'react';
import { CalendarCheck, Monitor } from 'lucide-react';
import { useSimulation } from '../../../context/SimulationContext';

export default function DefenseScheduledView() {
    const { sidangSchedule } = useSimulation();

    // Fallback if context is somehow null (shouldn't happen in normal flow)
    const schedule = sidangSchedule || {
        tanggal: 'Belum ditetapkan',
        waktu: '-',
        ruangan: '-',
        dosenPenguji1: '-',
        dosenPenguji2: '-',
        panduanLink: '#',
    };

    return (
        <div className="animate-in fade-in duration-500">
            <p className="mb-6 text-[14px] text-gray-500">
                Pengajuan verifikasi dokumen akhir dan jadwal sidang.
            </p>
            <div className="bg-white rounded-2xl p-[48px] max-w-[900px] border border-gray-200 mx-auto">
                {/* TOP SECTION */}
                <div className="mx-auto w-[80px] h-[80px] rounded-full bg-primary-pale flex items-center justify-center">
                    <CalendarCheck className="w-[40px] h-[40px] text-primary" />
                </div>
                <h2 className="text-center mt-[20px] font-bold text-[28px] text-[#1A1A1A]">
                    Jadwal Sidang Magang Anda
                </h2>
                <p className="text-center mt-[8px] text-gray-500 text-[14px] leading-relaxed max-w-[480px] mx-auto">
                    Berikut adalah detail jadwal pelaksanaan sidang magang Anda yang telah divalidasi oleh UPT PPAIP.
                </p>

                {/* BOTTOM SECTION */}
                <div className="mt-[40px] flex flex-col md:flex-row gap-8 items-start">
                    {/* LEFT COLUMN: DETAIL PELAKSANAAN */}
                    <div className="flex-1 w-full">
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
                                    className={`flex justify-between items-center py-[18px] px-[24px] ${idx !== arr.length - 1 ? 'border-b border-primary/10' : ''}`}
                                >
                                    <span className="text-gray-500 text-[14px] font-normal">{row.label}</span>
                                    <span className="font-bold text-[15px] text-[#1A1A1A] text-right max-w-[60%] leading-snug">
                                        {row.value}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* RIGHT COLUMN: PERSIAPAN SIDANG */}
                    <div className="w-full md:w-[280px] flex-shrink-0">
                        <h4 className="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-[16px]">
                            PERSIAPAN SIDANG
                        </h4>
                        <div className="bg-white border border-gray-200 rounded-xl p-[20px]">
                            <div className="flex items-center gap-[14px]">
                                <div className="w-[42px] h-[42px] bg-primary-pale rounded-xl flex-shrink-0 flex items-center justify-center">
                                    <Monitor className="w-[20px] h-[20px] text-primary" />
                                </div>
                                <div className="flex-1 flex flex-col">
                                    <span className="font-bold text-[14px] text-[#1A1A1A]">Panduan Presentasi</span>
                                    <span className="text-gray-500 text-[12px] mt-[2px] leading-tight">
                                        Pelajari tips dan teknis presentasi sidang.
                                    </span>
                                </div>
                            </div>
                            <button
                                onClick={() => window.open(schedule.panduanLink, '_blank')}
                                className="mt-[16px] w-full bg-primary text-white rounded-lg py-[10px] font-bold text-[13px] text-center hover:bg-primary-hover transition-colors"
                            >
                                Lihat Panduan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
