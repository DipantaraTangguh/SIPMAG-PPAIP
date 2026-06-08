import React from 'react';
import DashboardLayout from '../../Components/Layouts/DashboardLayout';
import { useAuth } from '../../context/AppContext';
import { useDefenseWorkflow } from '../../context/StudentWorkflowContext';
import { canAccessSidang } from '../../utils/accessUtils';
import DefenseLockedState from '../../Components/Fragments/defense/DefenseLockedState';
import DefenseFormView from '../../Components/Fragments/defense/DefenseFormView';
import DefenseSuccessView from '../../Components/Fragments/defense/DefenseSuccessView';
import DefenseScheduledView from '../../Components/Fragments/defense/DefenseScheduledView';
import DefenseCompletedView from '../../Components/Fragments/defense/DefenseCompletedView';

export default function InternshipDefensePage() {
    const { student } = useAuth();
    const { sidangSubmission, sidangSchedule } = useDefenseWorkflow();
    const accessStatus = student?.accessStatus;

    // View sidang cukup ngikut context biar state nggak dobel.
    const sidangView = (() => {
        if (accessStatus === 'SiklusSelesai') return 'completed';
        if (sidangSubmission?.status === 'Scheduled') return 'scheduled';
        if (sidangSubmission) return 'success'; // Pending — waiting for Kaprodi to schedule
        return 'form';
    })();

    if (!canAccessSidang(accessStatus)) {
        return (
            <DashboardLayout pageTitle="Sidang Magang">
                <div className="animate-in fade-in duration-500">
                    <DefenseLockedState accessStatus={accessStatus} />
                </div>
            </DashboardLayout>
        );
    }

    return (
        <DashboardLayout pageTitle="Sidang Magang">
            {sidangView === 'completed' && <DefenseCompletedView />}
            {sidangView === 'scheduled' && <DefenseScheduledView />}
            {sidangView === 'success' && <DefenseSuccessView />}
            {sidangView === 'form' && <DefenseFormView />}
        </DashboardLayout>
    );
}
