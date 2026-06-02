/**
 * Form1StatusPage.jsx
 * Status / read-only view of a submitted Form 1 submission.
 * Reads all data reactively from SimulationContext.
 * The right panel updates LIVE when simulation panel buttons are clicked.
 *
 * Route: /form1/status
 */
import React from 'react';
import { Navigate } from 'react-router-dom';
import { useSimulation } from '../../context/SimulationContext';
import DashboardLayout from '../../Components/Layouts/DashboardLayout';
import Form1SubmittedData from '../../Components/Fragments/form1/Form1SubmittedData';
import Form1StatusPanel from '../../Components/Fragments/form1/Form1StatusPanel';

export default function Form1StatusPage() {
    const { form1Submission, student } = useSimulation();

    // Guard: only redirect back if student truly hasn't submitted Form 1.
    // We check accessStatus instead of form1Submission because setState is async —
    // form1Submission might not be committed yet when navigating from the form.
    const status = student?.accessStatus;
    const hasNotSubmitted = !status || status === 'Unverified' || status === 'RejectedForm1';
    if (hasNotSubmitted && !form1Submission) {
        return <Navigate to="/form1" replace />;
    }

    // Show loading if we know form was submitted but data hasn't arrived in state yet
    if (!form1Submission) {
        return (
            <DashboardLayout pageTitle="Status Form Magang 01">
                <div className="flex items-center justify-center py-20 text-gray-400">
                    Memuat data form...
                </div>
            </DashboardLayout>
        );
    }

    // Build the formData object that the sub-components expect
    // form1Submission may come from API (jumlahSKS, skemaMagang) or old sim (jumlahSks, rencanaSkema)
    const f = form1Submission;
    const formData = {
        nama: f.nama ?? student?.name ?? '',
        nim: f.nim ?? student?.nim ?? '',
        programStudi: f.programStudi ?? student?.programStudi ?? '',
        semester: f.semester,
        tahunAkademik: f.tahunAkademik ?? student?.tahunAkademik ?? '',
        jumlahSks: f.jumlahSks ?? f.jumlahSKS,
        ipk: f.ipk,
        rencanaSkema: f.rencanaSkema ?? f.skemaMagang,
        topikTempat: f.topikTempat ?? f.topikMagang,
        output: f.output ?? f.outputTarget,
        submittedAt: f.submittedAt,
        // Approval-specific fields
        approverName: f.approver?.name,
        approverNidn: f.approver?.nidn,
        approverRole: f.approver?.role,
        approvalDate: f.approver?.approvalDate,
        pdfFileName: f.pdfFileName,
        pdfSize: f.pdfSize,
        pdfPath: f.pdfPath,
    };

    return (
        <DashboardLayout
            pageTitle="Status Form Magang 01"
        >
            {/* Subtitle */}
            <p className="mb-6 text-sm text-primary">
                Surat Keterangan Memenuhi Syarat Akademik
            </p>

            {/* Two-column layout */}
            <div className="grid grid-cols-1 items-start gap-6 xl:grid-cols-[1fr_420px]">
                {/* Left: submitted data */}
                <Form1SubmittedData formData={formData} />

                {/* Right: status panel — updates reactively */}
                <Form1StatusPanel
                    form1_status={form1Submission.status}
                    rejectionReason={form1Submission.rejectionReason}
                    pdfPath={form1Submission.pdfPath}
                    nim={student?.nim}
                    formData={formData}
                />
            </div>
        </DashboardLayout>
    );
}
