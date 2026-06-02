/**
 * src/pages/InternshipDefensePage.jsx
 * Thin shell page component for Sidang Magang.
 * Handles access-gating and delegates to extracted view components.
 *
 * InternshipDefensePage — 4 States:
 *   State 1: FORM      → DefenseFormView
 *   State 2: SUCCESS    → DefenseSuccessView
 *   State 3: SCHEDULED  → DefenseScheduledView
 *   State 4: COMPLETED  → DefenseCompletedView
 */
import React from 'react';
import DashboardLayout from '../../Components/Layouts/DashboardLayout';
import { useSimulation } from '../../context/SimulationContext';
import { canAccessSidang } from '../../utils/accessUtils';
import DefenseLockedState from '../../Components/Fragments/defense/DefenseLockedState';
import DefenseFormView from '../../Components/Fragments/defense/DefenseFormView';
import DefenseSuccessView from '../../Components/Fragments/defense/DefenseSuccessView';
import DefenseScheduledView from '../../Components/Fragments/defense/DefenseScheduledView';
import DefenseCompletedView from '../../Components/Fragments/defense/DefenseCompletedView';

export default function InternshipDefensePage() {
    const { student, sidangSubmission, sidangSchedule } = useSimulation();
    const accessStatus = student?.accessStatus;

    // Derive the current view entirely from context — no local state
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
