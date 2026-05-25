/**
 * components/sidang/SidangSuccessView.jsx
 * State 2: Success confirmation after sidang documents submitted.
 * Extracted from SidangMagangPage.jsx lines 403–461.
 */
import React from 'react';
import { Clock } from 'lucide-react';
import { useSimulation } from '../../context/SimulationContext';

export default function SidangSuccessView() {
    const { student, activeApplications } = useSimulation();

    const acceptedApp = activeApplications?.find(a => a.status === 'Diterima') || {
        company: 'Traveloka Indonesia',
        position: 'UI/UX Designer Intern'
    };
    const dpmName = student?.dpm?.name || 'Dita Nurmadewi S.Kom';

    return (
        <div className="animate-in fade-in duration-500">
            <p className="mb-6 text-[14px] text-gray-500">
                Pengajuan verifikasi dokumen akhir dan jadwal sidang.
            </p>
            <div className="bg-white rounded-2xl p-12 max-w-[800px] border border-gray-150 shadow-sm mx-auto flex flex-col items-center mt-2">
                <div className="w-[68px] h-[68px] rounded-full bg-amber-100 flex items-center justify-center mb-8">
                    <div className="w-[34px] h-[34px] bg-amber-500 rounded-full flex items-center justify-center">
                        <Clock className="w-5 h-5 text-white" />
                    </div>
                </div>
                
                <h2 className="text-[#1A1A1A] text-[24px] font-bold text-center max-w-[500px] leading-tight">
                    Pendaftaran Sidang Magang Anda Telah Diterima, Mohon Menunggu Jadwal Sidang
                </h2>
                
                <p className="text-gray-500 text-[14px] text-center mt-4 max-w-[500px]">
                    Pastikan Anda tetap memantau perkembangan informasi terkait jadwal sidang magang.
                </p>

                <div className="w-full mt-10">
                    <h4 className="text-[12px] font-bold text-gray-800 uppercase tracking-[0.08em] mb-4">
                        RINGKASAN MAGANG
                    </h4>
                    
                    <div className="bg-primary-pale/30 rounded-xl px-6 py-2">
                        <div className="flex justify-between items-center py-4 border-b border-primary-pale/50">
                            <span className="text-gray-500 text-[13px] font-medium">Perusahaan</span>
                            <span className="text-[#1A1A1A] text-[14px] font-bold">{acceptedApp.company || acceptedApp.companyName}</span>
                        </div>
                        <div className="flex justify-between items-center py-4 border-b border-primary-pale/50">
                            <span className="text-gray-500 text-[13px] font-medium">Posisi</span>
                            <span className="text-[#1A1A1A] text-[14px] font-bold">{acceptedApp.position}</span>
                        </div>
                        <div className="flex justify-between items-center py-4 border-b border-primary-pale/50">
                            <span className="text-gray-500 text-[13px] font-medium">Dosen Pembimbing</span>
                            <span className="text-[#1A1A1A] text-[14px] font-bold">{dpmName}</span>
                        </div>
                        <div className="flex justify-between items-center py-4">
                            <span className="text-gray-500 text-[13px] font-medium">Total Logbook</span>
                            <span className="bg-primary text-white px-3 py-1 rounded-full text-[11px] font-bold">
                                6/6 Entri Disetujui
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
