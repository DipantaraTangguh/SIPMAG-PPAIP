/**
 * src/pages/GuidancePage.jsx
 * Thin shell page component for Bimbingan & Logbook.
 * Handles access-gating and delegates to extracted components.
 *
 * View routing is driven by SimulationContext state:
 *   pengajuanPembimbing == null           → SupervisorRequestForm
 *   pengajuanPembimbing && !student.dpm   → RequestSubmittedView
 *   student.dpm && accessStatus=="HasDPM" → DpmAssignedView
 */
import React from 'react';
import DashboardLayout from '../../Components/Layouts/DashboardLayout';
import { useSimulation } from '../../context/SimulationContext';
import GuidanceLockedState from '../../Components/Fragments/guidance/GuidanceLockedState';
import GuidanceFullPage from '../../Components/Fragments/guidance/GuidanceFullPage';

export default function GuidancePage() {
    const { student } = useSimulation();
    const accessStatus = student?.accessStatus;

    const hasAccess = accessStatus === 'HasApplication' || accessStatus === 'HasDPM' || accessStatus === 'LogbookComplete';

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
