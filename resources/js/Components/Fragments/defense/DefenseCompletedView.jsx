/**
 * components/defense/DefenseCompletedView.jsx
 * State 4: Graduation card + Ringkasan Magang summary.
 * Extracted from InternshipDefensePage.jsx lines 252–310.
 */
import React from 'react';
import { GraduationCap } from 'lucide-react';
import { useSimulation } from '../../../context/SimulationContext';

export default function DefenseCompletedView() {
    const { student, pengajuanPembimbing, logbookEntries } = useSimulation();

    const perusahaan = pengajuanPembimbing?.namaPerusahaan || 'Perusahaan Magang';
    const posisi = pengajuanPembimbing?.lingkupMagang || pengajuanPembimbing?.jabatanPraktisi || 'Peserta Magang';
    const dosenPembimbing = student?.dpm?.name || 'Dosen Pembimbing';
    const approvedLogbook = (logbookEntries || []).filter(e => e.status === 'Disetujui').length;
    const totalRequired = 6;

    return (
        <div className="animate-in fade-in duration-500">
            <p className="mb-6 text-[14px] text-gray-500">
                Pengajuan verifikasi dokumen akhir dan jadwal sidang.
            </p>
            <div className="mx-auto mt-8 max-w-[860px] rounded-2xl border border-gray-200 bg-white p-6 sm:p-[48px] md:px-[56px]">
                {/* TOP SECTION */}
                <div className="mx-auto w-[80px] h-[80px] rounded-full bg-primary-pale flex items-center justify-center">
                    <GraduationCap className="w-[40px] h-[40px] text-primary" />
                </div>
                <h2 className="mt-[24px] text-center text-2xl font-bold leading-tight text-[#1A1A1A] sm:text-[30px]">
                    Selamat! Magang Anda Telah Selesai
                </h2>
                <p className="text-center mt-[12px] text-gray-500 text-[15px] leading-relaxed max-w-[520px] mx-auto">
                    Siklus magang Anda telah berhasil diselesaikan dengan predikat yang memuaskan. Terima kasih atas dedikasi dan kerja keras Anda selama program berlangsung.
                </p>

                {/* BOTTOM SECTION */}
                <div className="mt-[40px] w-full">
                    <h4 className="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-[16px]">
                        RINGKASAN MAGANG
                    </h4>
                    <div className="bg-primary-pale rounded-xl p-0 overflow-hidden">
                        {[
                            { label: 'Perusahaan', value: perusahaan },
                            { label: 'Posisi', value: posisi },
                            { label: 'Dosen Pembimbing', value: dosenPembimbing },
                        ].map((row, idx) => (
                            <div 
                                key={idx} 
                                className="flex flex-col gap-1 border-b border-primary/10 px-5 py-[20px] sm:flex-row sm:items-center sm:justify-between sm:px-[28px]"
                            >
                                <span className="text-gray-500 text-[14px] font-normal">{row.label}</span>
                                <span className="font-bold text-[15px] text-[#1A1A1A] text-right">
                                    {row.value}
                                </span>
                            </div>
                        ))}
                        <div className="flex flex-col gap-2 px-5 py-[20px] sm:flex-row sm:items-center sm:justify-between sm:px-[28px]">
                            <span className="text-gray-500 text-[14px] font-normal">Total Logbook</span>
                            <span className="bg-primary text-white text-[13px] font-bold rounded-full px-4 py-1.5 inline-block text-right">
                                {approvedLogbook}/{totalRequired} Entri Disetujui
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
