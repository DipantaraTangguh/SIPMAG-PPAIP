import React from 'react';
import { Navigate } from 'react-router-dom';
import { useSimulation } from '../../context/SimulationContext';
import DashboardLayout from '../../Components/Layouts/DashboardLayout';
import Form1Card from '../../Components/Fragments/form1/Form1Card';
import useForm1 from '../../hooks/useForm1';
import { FullScreenSpinner } from '../../Components/Elements/LoadingSpinner';

export default function Form1Page() {
    const { student } = useSimulation();
    const form1 = useForm1();

    // Form 1 cuma bisa diisi ulang kalau belum valid atau habis ditolak.
    const canFill = student?.accessStatus === 'Unverified' || student?.accessStatus === 'RejectedForm1';
    if (!canFill) {
        return <Navigate to="/form1/status" replace />;
    }

    // Saat submit, kunci layar biar user nggak dobel klik.
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
