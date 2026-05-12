/**
 * src/pages/SidangMagangPage.jsx
 * Thin shell page component for Sidang Magang.
 * Handles access-gating and delegates to extracted view components.
 *
 * SidangMagangPage — 4 States:
 *   State 1: FORM      → SidangFormView
 *   State 2: SUCCESS    → SidangSuccessView
 *   State 3: SCHEDULED  → SidangScheduledView
 *   State 4: COMPLETED  → SidangCompletedView
 */
import React from 'react';
import DashboardLayout from '../layouts/DashboardLayout';
import { useSimulation } from '../context/SimulationContext';
import { canAccessSidang } from '../utils/accessUtils';
import SidangLockedState from '../components/sidang/SidangLockedState';
import SidangFormView from '../components/sidang/SidangFormView';
import SidangSuccessView from '../components/sidang/SidangSuccessView';
import SidangScheduledView from '../components/sidang/SidangScheduledView';
import SidangCompletedView from '../components/sidang/SidangCompletedView';

export default function SidangMagangPage() {
    const { student, sidangSubmission, sidangSchedule } = useSimulation();
    const accessStatus = student?.accessStatus;

    // Derive the current view entirely from context — no local state
    const sidangView = (() => {
        if (accessStatus === 'SiklusSelesai') return 'completed';
        if (sidangSchedule) return 'scheduled';
        if (sidangSubmission) return 'success';
        return 'form';
    })();

    if (!canAccessSidang(accessStatus)) {
        return (
            <DashboardLayout pageTitle="Sidang Magang">
                <div className="animate-in fade-in duration-500">
                    <SidangLockedState accessStatus={accessStatus} />
                </div>
            </DashboardLayout>
        );
    }

    return (
        <DashboardLayout pageTitle="Sidang Magang">
            {sidangView === 'completed' && <SidangCompletedView />}
            {sidangView === 'scheduled' && <SidangScheduledView />}
            {sidangView === 'success' && <SidangSuccessView />}
            {sidangView === 'form' && <SidangFormView />}
        </DashboardLayout>
    );
}
