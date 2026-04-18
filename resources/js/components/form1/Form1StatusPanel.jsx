/**
 * Form1StatusPanel.jsx
 * Right-column wrapper that conditionally renders the correct status panel
 * (Pending / Approved / Rejected) plus always-shown cards.
 *
 * Layout per state:
 *   PendingReview  → PendingPanel  + BackButton + HelpCard
 *   ApprovedForm1  → ApprovedPanel + HelpCard  (no BackButton — CTA is inside panel)
 *   RejectedForm1  → RejectedPanel + BackButton + HelpCard
 *
 * @prop {string}      form1_status    — "PendingReview" | "ApprovedForm1" | "RejectedForm1"
 * @prop {string|null} rejectionReason — Kaprodi rejection reason (RejectedForm1 only).
 * @prop {string|null} pdfPath         — Signed PDF download path (ApprovedForm1 only).
 * @prop {string}      nim             — Student NIM.
 * @prop {object}      formData        — Full submitted form data (for rejected re-fill).
 */
import React from 'react';
import Form1PendingPanel from './Form1PendingPanel';
import Form1ApprovedPanel from './Form1ApprovedPanel';
import Form1RejectedPanel from './Form1RejectedPanel';
import Form1BackButton from './Form1BackButton';
import Form1HelpCard from './Form1HelpCard';

export default function Form1StatusPanel({
    form1_status,
    rejectionReason,
    pdfPath,
    nim,
    formData,
}) {
    const isApproved = form1_status === 'ApprovedForm1';

    return (
        <div className="flex flex-col gap-4">
            {/* Conditional status panel */}
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
                />
            )}

            {form1_status === 'RejectedForm1' && (
                <Form1RejectedPanel
                    rejectionReason={rejectionReason}
                    formData={formData}
                />
            )}

            {/* Back button — shown for Pending and Rejected only */}
            {!isApproved && <Form1BackButton />}

            {/* Help card — always shown */}
            <Form1HelpCard />
        </div>
    );
}
