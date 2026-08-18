import React from 'react';
import Form1PendingPanel from './Form1PendingPanel';
import Form1ApprovedPanel from './Form1ApprovedPanel';
import Form1RejectedPanel from './Form1RejectedPanel';
import Form1BackButton from './Form1BackButton';
import Form1HelpCard from './Form1HelpCard';
import Form1CompletedPanel from './Form1CompletedPanel';
import Form1NonWajibDonePanel from './Form1NonWajibDonePanel';
import Form1KonfirmasiPanel from './Form1KonfirmasiPanel';

export default function Form1StatusPanel({
    form1_status,
    rejectionReason,
    pdfPath,
    nim,
    formData,
}) {
    const isApproved = form1_status === 'ApprovedForm1';
    const isCompleted = form1_status === 'CycleCompleted';
    const isNonWajibDone = form1_status === 'ElectiveCompleted';
    // Non-wajib boleh lapor sendiri kalau sudah dapat tempat magang: dari
    // ApprovedForm1 (dapat sendiri, tanpa surat pengantar/portal) maupun dari
    // HasApplication (melamar lewat portal lalu diterima langsung perusahaan).
    // Tanpa ini mereka menunggu Form 2 disetujui atau PPAIP mengubah status.
    const isNonWajib = formData?.jenisMagang === 'non_wajib';
    const isSelfReporting =
        isNonWajib && ['ApprovedForm1', 'HasApplication'].includes(form1_status);
    const isAwaitingConfirmation =
        form1_status === 'AwaitingConfirmation' || isSelfReporting;

    return (
        <div className="flex flex-col gap-4">
            {form1_status === 'PendingReview' && <Form1PendingPanel />}

            {isApproved && (
                <Form1ApprovedPanel
                    pdfPath={formData.pdfPath}
                    pdfFileName={formData.pdfFileName}
                    pdfSize={formData.pdfSize}
                    approverName={formData.approverName}
                    approverNidn={formData.approverNidn}
                    approverRole={formData.approverRole}
                    approvalDate={formData.approvalDate}
                    studentNim={nim}
                />
            )}

            {form1_status === 'RejectedForm1' && (
                <Form1RejectedPanel
                    rejectionReason={rejectionReason}
                    formData={formData}
                />
            )}
            {isCompleted && <Form1CompletedPanel />}
            {isNonWajibDone && <Form1NonWajibDonePanel />}
            {isAwaitingConfirmation && <Form1KonfirmasiPanel />}
            {!isApproved && !isCompleted && !isNonWajibDone && !isAwaitingConfirmation && <Form1BackButton />}
            <Form1HelpCard />
        </div>
    );
}
