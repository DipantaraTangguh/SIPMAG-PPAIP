/**
 * src/pages/BimbinganPage.jsx
 * Thin shell page component for Bimbingan & Logbook.
 * Handles access-gating and delegates to extracted components.
 *
 * View routing is driven by SimulationContext state:
 *   pengajuanPembimbing == null           → PengajuanPembimbingForm
 *   pengajuanPembimbing && !student.dpm   → PengajuanSubmittedView
 *   student.dpm && accessStatus=="HasDPM" → PengajuanDPMAssignedView
 */
import React from 'react';
import DashboardLayout from '../layouts/DashboardLayout';
import { useSimulation } from '../context/SimulationContext';
import BimbinganLockedState from '../components/bimbingan/BimbinganLockedState';
import BimbinganFullPage from '../components/bimbingan/BimbinganFullPage';

export default function BimbinganPage() {
    const { student } = useSimulation();
    const accessStatus = student?.accessStatus;

    const hasAccess = accessStatus === 'HasApplication' || accessStatus === 'HasDPM' || accessStatus === 'LogbookComplete';

    if (!hasAccess) {
        return (
            <DashboardLayout pageTitle="Bimbingan & Logbook">
                <div className="animate-in fade-in duration-500">
                    <BimbinganLockedState accessStatus={accessStatus} />
                </div>
            </DashboardLayout>
        );
    }

    return (
        <DashboardLayout pageTitle="Bimbingan & Logbook">
            <BimbinganFullPage />
        </DashboardLayout>
    );
}
