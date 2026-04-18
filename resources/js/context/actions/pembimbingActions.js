/**
 * context/actions/pembimbingActions.js
 * Pengajuan Pembimbing and DPM assignment actions.
 */
import { useCallback } from 'react';
import { MOCK_DPM } from '../../constants/simulationConstants';

export function usePembimbingActions(setState) {
    const submitPengajuanPembimbing = useCallback((formData) => {
        setState((p) => ({
            ...p,
            pengajuanPembimbing: {
                namaPerusahaan: formData.namaPerusahaan,
                namaPraktisi: formData.namaPraktisi,
                jabatanPraktisi: formData.jabatanPraktisi,
                noTelepon: formData.noTelepon,
                email: formData.email,
                mulaiMagang: formData.mulaiMagang,
                selesaiMagang: formData.selesaiMagang,
                loaFileName: formData.loaFile?.name || 'LoA_Dokumen.pdf',
                loaFileSize: formData.loaFile
                    ? `${(formData.loaFile.size / (1024 * 1024)).toFixed(1)} MB`
                    : '1.2 MB',
                submittedAt: new Date().toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric',
                }),
            },
            notifications: [
                {
                    id: Date.now(),
                    message: 'Pengajuan Pembimbing Magang berhasil dikirim. Menunggu penugasan DPM dari Kaprodi.',
                    time: 'Baru saja',
                },
                ...p.notifications,
            ],
        }));
    }, [setState]);

    const assignDPM = useCallback(() => {
        setState((p) => ({
            ...p,
            student: {
                ...p.student,
                accessStatus: 'HasDPM',
                dpm: MOCK_DPM,
            },
            notifications: [
                {
                    id: Date.now(),
                    message: `Dosen Pembimbing Magang telah ditugaskan: ${MOCK_DPM.name}. Mulai isi logbook sekarang.`,
                    time: 'Baru saja',
                },
                ...p.notifications,
            ],
        }));
    }, [setState]);

    return { submitPengajuanPembimbing, assignDPM };
}
