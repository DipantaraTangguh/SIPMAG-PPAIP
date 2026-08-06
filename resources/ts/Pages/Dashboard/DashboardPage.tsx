import React from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AppContext';
import {
    useDefenseWorkflow,
    useForm1Workflow,
    useLogbookWorkflow,
    useWorkflowNotifications,
} from '../../context/StudentWorkflowContext';
import DashboardLayout from '../../Components/Layouts/DashboardLayout';

import WelcomeBanner from '../../Components/Fragments/dashboard/WelcomeBanner';
import CycleStepper from '../../Components/Fragments/dashboard/CycleStepper';
import DpmCard from '../../Components/Fragments/dashboard/DpmCard';
import NotificationCard from '../../Components/Fragments/dashboard/NotificationCard';
import QuickActionButton from '../../Components/Fragments/dashboard/QuickActionButton';
import LogbookProgressCard from '../../Components/Fragments/dashboard/LogbookProgressCard';
import TipsMagangCard from '../../Components/Fragments/dashboard/TipsMagangCard';

const mockTips = [
    'Jangan lupa isi logbook sebagai syarat untuk bisa melakukan sidang magang.',
    'Gunakan foto profil profesional untuk meningkatkan kepercayaan mitra magang.',
];
function deriveStep(status) {
    switch (status) {
        case 'Unverified':
        case 'PendingReview':
        case 'RejectedForm1':
            return 1;
        case 'ApprovedForm1':
            return 2;
        case 'HasApplication':
        case 'MenungguKonfirmasi':
            return 3;
        case 'HasDPM':
            return 4;
        case 'LogbookComplete':
        case 'MenungguSidang':
            return 5;
        case 'SiklusSelesai':
        case 'SelesaiNonWajib':
            return 6; // All 5 steps completed
        default:
            return 1;
    }
}

// Non-wajib berhenti di konfirmasi LoA — tanpa DPM/logbook/sidang.
function deriveNonWajibStep(status) {
    switch (status) {
        case 'Unverified':
        case 'PendingReview':
        case 'RejectedForm1':
            return 1;
        case 'ApprovedForm1':
        case 'HasApplication':
            return 2;
        case 'MenungguKonfirmasi':
            return 3;
        case 'SelesaiNonWajib':
            return 4; // 3 langkah selesai
        default:
            return 1;
    }
}

export default function DashboardPage() {
    const { student } = useAuth();
    const { notifications } = useWorkflowNotifications();
    const { logbookEntries } = useLogbookWorkflow();
    const { sidangSchedule } = useDefenseWorkflow();
    const { form1Submission } = useForm1Workflow();
    const navigate = useNavigate();

    const name = student?.name;
    const accessStatus = student?.accessStatus;
    const isNonWajib = form1Submission?.jenisMagang === 'non_wajib';
    const logbookCount = (logbookEntries || []).filter(e => e.status === 'Disetujui' || e.status === 'Approved').length;
    const dpm = student?.dpm;
    const currentStep = isNonWajib ? deriveNonWajibStep(accessStatus) : deriveStep(accessStatus);

    const handleQuickAction = () => {
        switch (accessStatus) {
            case 'Unverified':
            case 'RejectedForm1':
                navigate('/form1');
                break;
            case 'ApprovedForm1':
                navigate('/portal');
                break;
            case 'HasApplication':
                // Non-wajib nggak punya Bimbingan/Logbook -- arahkan ke panel
                // konfirmasi magang di Profil, bukan ke menu yang terkunci.
                navigate(isNonWajib ? '/form1/status' : '/guidance');
                break;
            case 'HasDPM':
                navigate('/guidance');
                break;
            case 'LogbookComplete':
            case 'MenungguSidang':
                navigate('/defense');
                break;
            case 'SiklusSelesai':
                navigate('/defense');
                break;
            case 'SelesaiNonWajib':
            case 'MenungguKonfirmasi':
                navigate('/form1/status');
                break;
            default:
                break;
        }
    };

    return (
        <DashboardLayout>
            <div className="flex flex-col gap-6">
                <WelcomeBanner name={name} />
                <CycleStepper currentStep={currentStep} variant={isNonWajib ? 'non_wajib' : 'wajib'} />
                <div className="grid grid-cols-1 gap-6 xl:grid-cols-[1fr_340px]">
                    <div className="flex flex-col gap-6">
                        <DpmCard dpm={dpm} />
                        <NotificationCard notifications={notifications} />
                    </div>
                    <div className="flex flex-col gap-6">
                        <QuickActionButton
                            accessStatus={accessStatus}
                            onClick={handleQuickAction}
                            label={
                                accessStatus === 'MenungguSidang' && sidangSchedule
                                    ? 'Lihat Jadwal Sidang'
                                    : accessStatus === 'HasApplication' && isNonWajib
                                        ? 'Konfirmasi Magang'
                                        : undefined
                            }
                            disabled={accessStatus === 'MenungguSidang' && sidangSchedule ? false : undefined}
                            style={accessStatus === 'MenungguSidang' && sidangSchedule ? 'primary' : undefined}
                        />
                        <LogbookProgressCard approvedCount={logbookCount} />
                        <TipsMagangCard tips={mockTips} />
                    </div>
                </div>
            </div>
        </DashboardLayout>
    );
}
