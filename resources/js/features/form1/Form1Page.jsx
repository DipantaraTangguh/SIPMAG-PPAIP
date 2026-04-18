/**
 * Form1Page.jsx
 * Page for "Form Magang-01: Surat Keterangan Memenuhi Syarat Akademik".
 * Accessed from the Beranda quickaction "Isi Form 1".
 * The Profil nav item is active in the sidebar.
 *
 * Guard: If student has already submitted (status != Unverified/RejectedForm1),
 * redirect to the status page instead.
 */
import React from 'react';
import { Navigate } from 'react-router-dom';
import { useSimulation } from '../../context/SimulationContext';
import DashboardLayout from '../../layouts/DashboardLayout';
import Form1Card from '../../components/form1/Form1Card';
import useForm1 from '../../hooks/useForm1';
import { FullScreenSpinner } from '../../components/LoadingSpinner';

export default function Form1Page() {
    const { student } = useSimulation();
    const form1 = useForm1();

    // Guard: only allow filling if Unverified or RejectedForm1
    const canFill = student.accessStatus === 'Unverified' || student.accessStatus === 'RejectedForm1';
    if (!canFill) {
        return <Navigate to="/form1/status" replace />;
    }

    // Show full-screen spinner during submit
    if (form1.isSubmitting) {
        return <FullScreenSpinner />;
    }

    return (
        <DashboardLayout
            pageTitle="Form Magang-01: Surat Keterangan Memenuhi Syarat Akademik"
        >
            <Form1Card {...form1} />
        </DashboardLayout>
    );
}
