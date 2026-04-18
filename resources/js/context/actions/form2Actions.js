/**
 * context/actions/form2Actions.js
 * Form 2 (Magang Mandiri) lifecycle actions.
 */
import { useCallback } from 'react';
import { MOCK_FORM2_REJECTION_REASON } from '../../constants/simulationConstants';

export function useForm2Actions(setState) {
    const submitForm2 = useCallback((formData) => {
        const newSubmission = {
            id: Date.now(),
            companyName: formData.namaPerusahaan,
            position: formData.lingkupMagang,
            alamat: formData.alamatPerusahaan,
            tanggalMulai: formData.tanggalMulai,
            tanggalSelesai: formData.tanggalSelesai,
            status: 'Menunggu Review',
            submittedAt: new Date().toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short',
                year: 'numeric',
            }),
            pdfAvailable: false,
            rejectionReason: null,
            pdfPath: null,
            rawData: formData,
        };
        setState((p) => ({
            ...p,
            form2Submissions: [newSubmission, ...p.form2Submissions],
            notifications: [
                {
                    id: Date.now(),
                    message: `Pengajuan Form 2 ke ${formData.namaPerusahaan} berhasil dikirim. Menunggu persetujuan PPAIP.`,
                    time: 'Baru saja',
                },
                ...p.notifications,
            ],
        }));
    }, [setState]);

    const simulateApproveForm2 = useCallback((submissionId) => {
        setState((p) => {
            const updatedSubmissions = p.form2Submissions.map((s) =>
                s.id === submissionId
                    ? {
                          ...s,
                          status: 'Disetujui',
                          pdfAvailable: true,
                          pdfPath: '#',
                          pdfFileName: `Form2_${s.companyName.replace(/\s/g, '_')}.pdf`,
                          approvedAt: new Date().toLocaleDateString('id-ID'),
                      }
                    : s
            );

            const hasApprovedForm2 = updatedSubmissions.some((s) => s.status === 'Disetujui');
            const approvedCompany = p.form2Submissions.find((s) => s.id === submissionId)?.companyName;

            return {
                ...p,
                form2Submissions: updatedSubmissions,
                student: hasApprovedForm2
                    ? { ...p.student, accessStatus: 'HasApplication', isIndependent: true }
                    : p.student,
                notifications: [
                    {
                        id: Date.now(),
                        message: hasApprovedForm2
                            ? `Form 2 untuk ${approvedCompany} telah disetujui. Silakan ajukan Dosen Pembimbing Magang Anda.`
                            : `Form 2 untuk ${approvedCompany} telah disetujui. Unduh PDF sekarang.`,
                        time: 'Baru saja',
                    },
                    ...p.notifications,
                ],
            };
        });
    }, [setState]);

    const simulateRejectForm2 = useCallback((submissionId, reason) => {
        setState((p) => ({
            ...p,
            form2Submissions: p.form2Submissions.map((s) =>
                s.id === submissionId
                    ? {
                          ...s,
                          status: 'Ditolak',
                          pdfAvailable: false,
                          rejectionReason: reason || MOCK_FORM2_REJECTION_REASON,
                      }
                    : s
            ),
            notifications: [
                {
                    id: Date.now(),
                    message: `Form 2 untuk ${p.form2Submissions.find((s) => s.id === submissionId)?.companyName} ditolak. Periksa alasan penolakan.`,
                    time: 'Baru saja',
                },
                ...p.notifications,
            ],
        }));
    }, [setState]);

    return { submitForm2, simulateApproveForm2, simulateRejectForm2 };
}
