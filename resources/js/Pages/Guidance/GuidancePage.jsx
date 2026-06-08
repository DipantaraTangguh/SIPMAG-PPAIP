import React from 'react';
import DashboardLayout from '../../Components/Layouts/DashboardLayout';
import { useAuth } from '../../context/AuthContext';
import GuidanceLockedState from '../../Components/Fragments/guidance/GuidanceLockedState';
import GuidanceFullPage from '../../Components/Fragments/guidance/GuidanceFullPage';

export default function GuidancePage() {
    const { student } = useAuth();
    const accessStatus = student?.accessStatus;

    const hasAccess = 
        accessStatus === 'HasApplication' || 
        accessStatus === 'HasDPM' || 
        accessStatus === 'LogbookComplete' || 
        accessStatus === 'MenungguSidang' || 
        accessStatus === 'SiklusSelesai';

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
