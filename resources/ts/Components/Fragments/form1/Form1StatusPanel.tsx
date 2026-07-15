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
    const isCompleted = form1_status === 'SiklusSelesai';
    const isNonWajibDone = form1_status === 'SelesaiNonWajib';
    const isAwaitingConfirmation = form1_status === 'MenungguKonfirmasi';

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
