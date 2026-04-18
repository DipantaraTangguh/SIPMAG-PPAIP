/**
 * components/bimbingan/BimbinganFullPage.jsx
 * Tab switcher between Pengajuan Pembimbing and Logbook.
 * Extracted from BimbinganPage.jsx lines 1118–1219.
 */
import React, { useState } from 'react';
import { Clock } from 'lucide-react';
import { useSimulation } from '../../context/SimulationContext';
import PengajuanPembimbingForm from './PengajuanPembimbingForm';
import PengajuanSubmittedView from './PengajuanSubmittedView';
import PengajuanDPMAssignedView from './PengajuanDPMAssignedView';
import LogbookTabContent from './LogbookTabContent';

export default function BimbinganFullPage() {
    const {
        student,
        pengajuanPembimbing,
        submitPengajuanPembimbing
    } = useSimulation();

    const [activeTab, setActiveTab] = useState('pengajuan');

    const canAccessLogbook = student.accessStatus === 'HasDPM' || student.accessStatus === 'LogbookComplete';

    // Determine which view to show based on context state
    const pengajuanView = (() => {
        if (student.dpm && (student.accessStatus === 'HasDPM' || student.accessStatus === 'LogbookComplete')) {
            return 'dpm_assigned';
        }
        if (pengajuanPembimbing && !student.dpm) {
            return 'submitted';
        }
        return 'form';
    })();

    return (
        <div className="animate-in fade-in duration-500">
            <div className="border-b border-gray-200">
                <nav className="-mb-px flex space-x-8">
                    <button
                        onClick={() => setActiveTab('pengajuan')}
                        className={`whitespace-nowrap border-b-2 px-1 py-4 text-sm font-bold ${
                            activeTab === 'pengajuan'
                                ? 'border-primary text-primary'
                                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'
                        }`}
                    >
                        Pengajuan Pembimbing
                    </button>
                    <button
                        onClick={() => setActiveTab('logbook')}
                        className={`whitespace-nowrap border-b-2 px-1 py-4 text-sm font-bold cursor-pointer ${
                            activeTab === 'logbook'
                                ? 'border-primary text-primary font-bold'
                                : canAccessLogbook
                                    ? 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'
                                    : 'border-transparent text-gray-400 cursor-default'
                        }`}
                    >
                        Logbook
                    </button>
                </nav>
            </div>

            {activeTab === 'pengajuan' && (
                <>
                    {pengajuanView === 'form' && (
                        <PengajuanPembimbingForm
                            onSubmit={submitPengajuanPembimbing}
                        />
                    )}
                    {pengajuanView === 'submitted' && (
                        <PengajuanSubmittedView
                            data={pengajuanPembimbing}
                            studentName={student.name}
                            studentNim={student.nim}
                        />
                    )}
                    {pengajuanView === 'dpm_assigned' && (
                        <PengajuanDPMAssignedView
                            dpm={student.dpm}
                            onGoToLogbook={() => setActiveTab('logbook')}
                        />
                    )}
                </>
            )}

            {activeTab === 'logbook' && (
                canAccessLogbook ? (
                    <LogbookTabContent />
                ) : (
                    <div className="mx-auto mt-12 w-full max-w-[560px]">
                        <div className="rounded-xl border border-gray-200 bg-white p-12 text-center">
                            <div className="mx-auto flex h-[56px] w-[56px] items-center justify-center rounded-full bg-amber-100">
                                <Clock className="h-10 w-10 text-amber-500" />
                            </div>
                            <h2 className="mt-4 text-[20px] font-bold text-[#1A1A1A]">
                                Menunggu Penugasan DPM
                            </h2>
                            <p className="mt-2 text-[14px] leading-relaxed text-gray-500">
                                Logbook baru dapat diisi setelah Kaprodi menetapkan Dosen Pembimbing Magang (DPM) untuk Anda. Proses ini sedang berlangsung.
                            </p>
                            <button
                                onClick={() => setActiveTab('pengajuan')}
                                className="mt-6 rounded-lg bg-primary px-6 py-3 font-bold text-white hover:bg-primary-hover"
                            >
                                Lihat Status Pengajuan →
                            </button>
                        </div>
                    </div>
                )
            )}
        </div>
    );
}
