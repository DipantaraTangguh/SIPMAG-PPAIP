import React from 'react';
import { Briefcase } from 'lucide-react';
import DashboardLayout from '../../Components/Layouts/DashboardLayout';
import { useAuth } from '../../context/AppContext';
import { useForm1Workflow } from '../../context/StudentWorkflowContext';
import GuidanceLockedState from '../../Components/Fragments/guidance/GuidanceLockedState';
import GuidanceFullPage from '../../Components/Fragments/guidance/GuidanceFullPage';

export default function GuidancePage() {
    const { student } = useAuth();
    const { form1Submission } = useForm1Workflow();
    const accessStatus = student?.accessStatus;

    const hasAccess =
        accessStatus === 'HasApplication' ||
        accessStatus === 'HasDPM' ||
        accessStatus === 'LogbookComplete' ||
        accessStatus === 'MenungguSidang' ||
        accessStatus === 'SiklusSelesai';

    // Magang non-wajib berhenti di Form 2 / lamaran mitra: tidak ada tahap
    // DPM, logbook, maupun sidang.
    if (form1Submission?.jenisMagang === 'non_wajib') {
        return (
            <DashboardLayout pageTitle="Bimbingan & Logbook">
                <div className="flex flex-col items-center rounded-xl border border-gray-200 bg-white px-6 py-16 text-center">
                    <div className="flex h-16 w-16 items-center justify-center rounded-full bg-primary/10">
                        <Briefcase className="h-8 w-8 text-primary" />
                    </div>
                    <h3 className="mt-4 text-lg font-bold text-gray-900">
                        Tidak Ada Tahap Bimbingan untuk Magang Non-Wajib
                    </h3>
                    <p className="mt-2 max-w-md text-sm text-gray-500">
                        Siklus magang non-wajib berhenti setelah surat pengantar
                        (Form 2) disetujui atau lamaran mitra Anda diterima —
                        tanpa tahap DPM, logbook, maupun sidang.
                    </p>
                </div>
            </DashboardLayout>
        );
    }

    if (!hasAccess) {
        return (
            <DashboardLayout pageTitle="Bimbingan & Logbook">
                <div className="animate-in fade-in duration-500">
                    <GuidanceLockedState accessStatus={accessStatus} />
                </div>
            </DashboardLayout>
        );
    }

    return (
        <DashboardLayout pageTitle="Bimbingan & Logbook">
            <GuidanceFullPage />
        </DashboardLayout>
    );
}
