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
import DashboardLayout from '../../layouts/DashboardLayout';
import Form1SubmittedData from '../../components/form1/Form1SubmittedData';
import Form1StatusPanel from '../../components/form1/Form1StatusPanel';

export default function Form1StatusPage() {
    const { form1Submission, student } = useSimulation();

    // Guard: if no submission exists, redirect to form fill page
    if (!form1Submission) {
        return <Navigate to="/form1" replace />;
    }

    // Build the formData object that the sub-components expect
    const formData = {
        nama: form1Submission.nama ?? student.name,
        nim: form1Submission.nim ?? student.nim,
        programStudi: form1Submission.programStudi ?? student.programStudi,
        semester: form1Submission.semester,
        tahunAkademik: form1Submission.tahunAkademik,
        jumlahSks: form1Submission.jumlahSks,
        ipk: form1Submission.ipk,
        rencanaSkema: form1Submission.rencanaSkema,
        topikTempat: form1Submission.topikTempat,
        output: form1Submission.output,
        submittedAt: form1Submission.submittedAt,
        // Approval-specific fields
        approverName: form1Submission.approver?.name,
        approverNidn: form1Submission.approver?.nidn,
        approverRole: form1Submission.approver?.role,
        approvalDate: form1Submission.approver?.approvalDate,
        pdfFileName: form1Submission.pdfFileName,
        pdfSize: form1Submission.pdfSize,
        pdfPath: form1Submission.pdfPath,
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
            <div className="grid grid-cols-[1fr_420px] items-start gap-6">
                {/* Left: submitted data */}
                <Form1SubmittedData formData={formData} />

                {/* Right: status panel — updates reactively */}
                <Form1StatusPanel
                    form1_status={form1Submission.status}
                    rejectionReason={form1Submission.rejectionReason}
                    pdfPath={form1Submission.pdfPath}
                    nim={student.nim}
                    formData={formData}
                />
            </div>
        </DashboardLayout>
    );
}
