import React, { useState } from 'react';
import { Clock } from 'lucide-react';
import { useAuth } from '../../../context/AppContext';
import { useGuidanceWorkflow } from '../../../context/StudentWorkflowContext';
import SupervisorRequestForm from './SupervisorRequestForm';
import RequestSubmittedView from './RequestSubmittedView';
import DpmAssignedView from './DpmAssignedView';
import LogbookTabContent from './LogbookTabContent';

export default function GuidanceFullPage() {
    const { student } = useAuth();
    const {
        pengajuanPembimbing,
        submitPengajuanPembimbing,
    } = useGuidanceWorkflow();

    const [activeTab, setActiveTab] = useState('pengajuan');

    const canAccessLogbook = 
        student?.accessStatus === 'HasDPM' || 
        student?.accessStatus === 'LogbookComplete' || 
        student?.accessStatus === 'MenungguSidang' || 
        student?.accessStatus === 'SiklusSelesai';

    // State context nentuin layar bimbingan yang tampil.
    const pengajuanView = (() => {
        if (student?.dpm && (
            student?.accessStatus === 'HasDPM' || 
            student?.accessStatus === 'LogbookComplete' || 
            student?.accessStatus === 'MenungguSidang' || 
            student?.accessStatus === 'SiklusSelesai'
        )) {
            return 'dpm_assigned';
        }
        if (pengajuanPembimbing && !student?.dpm) {
            return 'submitted';
        }
        return 'form';
    })();

    return (
        <div className="animate-in fade-in duration-500">
            <div className="overflow-x-auto border-b border-gray-200">
                <nav className="-mb-px flex min-w-max space-x-6 sm:space-x-8">
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
                        <SupervisorRequestForm
                            onSubmit={submitPengajuanPembimbing}
                        />
                    )}
                    {pengajuanView === 'submitted' && (
                        <RequestSubmittedView
                            data={pengajuanPembimbing}
                            studentName={student?.name}
                            studentNim={student?.nim}
                        />
                    )}
                    {pengajuanView === 'dpm_assigned' && (
                        <DpmAssignedView
                            dpm={student?.dpm}
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
                        <div className="rounded-xl border border-gray-200 bg-white p-6 text-center sm:p-12">
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
                                className="mt-6 w-full rounded-lg bg-primary px-6 py-3 font-bold text-white hover:bg-primary-hover sm:w-auto"
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
