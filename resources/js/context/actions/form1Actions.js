/**
 * context/actions/form1Actions.js
 * Form 1 lifecycle actions for the simulation context.
 */
import { useCallback } from 'react';
import { MOCK_FORM1_APPROVER, MOCK_FORM1_REJECTION_REASON } from '../../constants/simulationConstants';

export function useForm1Actions(setState) {
    const submitForm1 = useCallback((formData) => {
        const submission = {
            ...formData,
            submittedAt: new Date().toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric',
            }),
            status: 'PendingReview',
            rejectionReason: null,
            approver: null,
            pdfFileName: null,
            pdfSize: null,
            pdfPath: null,
        };
        setState((p) => ({
            ...p,
            form1Submission: submission,
            student: { ...p.student, accessStatus: 'PendingReview' },
            notifications: [
                {
                    id: Date.now(),
                    message: 'Form 1 berhasil diajukan. Menunggu persetujuan Kaprodi.',
                    time: 'Baru saja',
                },
                ...p.notifications,
            ],
        }));
    }, [setState]);

    const simulateApproveForm1 = useCallback(() => {
        setState((p) => ({
            ...p,
            form1Submission: {
                ...p.form1Submission,
                status: 'ApprovedForm1',
                approver: MOCK_FORM1_APPROVER,
                pdfFileName: `Form_Magang_01_${p.student.nim}.pdf`,
                pdfSize: '1.2 MB',
                pdfPath: '#',
            },
            student: { ...p.student, accessStatus: 'ApprovedForm1' },
            notifications: [
                {
                    id: Date.now(),
                    message: 'Form 1 Anda telah disetujui oleh Kaprodi. Silakan unduh dokumen.',
                    time: 'Baru saja',
                },
                ...p.notifications,
            ],
        }));
    }, [setState]);

    const simulateRejectForm1 = useCallback(() => {
        setState((p) => ({
            ...p,
            form1Submission: {
                ...p.form1Submission,
                status: 'RejectedForm1',
                rejectionReason: MOCK_FORM1_REJECTION_REASON,
                approver: null,
                pdfPath: null,
            },
            student: { ...p.student, accessStatus: 'RejectedForm1' },
            notifications: [
                {
                    id: Date.now(),
                    message: 'Form 1 Anda ditolak. Periksa alasan penolakan dan ajukan ulang.',
                    time: 'Baru saja',
                },
                ...p.notifications,
            ],
        }));
    }, [setState]);

    const resetForm1 = useCallback(() => {
        setState((p) => ({
            ...p,
            form1Submission: null,
            student: { ...p.student, accessStatus: 'Unverified' },
        }));
    }, [setState]);

    return { submitForm1, simulateApproveForm1, simulateRejectForm1, resetForm1 };
}
