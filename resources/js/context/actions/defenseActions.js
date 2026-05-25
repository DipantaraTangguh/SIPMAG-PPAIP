/**
 * context/actions/sidangActions.js
 * Sidang submission, schedule assignment, cycle completion, and full reset actions.
 */
import { useCallback } from 'react';
import { MOCK_SIDANG_SCHEDULE } from '../../constants/simulationConstants';

export function useSidangActions(setState) {
    const submitSidang = useCallback((filesData) => {
        const serializeFile = (f) => f ? { name: f.name, size: `${(f.size / (1024 * 1024)).toFixed(1)} MB` } : null;
        setState((p) => ({
            ...p,
            sidangSubmission: {
                laporanAkhir: serializeFile(filesData.laporanAkhir),
                posterPresentasi: serializeFile(filesData.posterPresentasi),
                fotoKegiatan1: serializeFile(filesData.fotoKegiatan1),
                fotoKegiatan2: serializeFile(filesData.fotoKegiatan2),
                krsMataKuliah: serializeFile(filesData.krsMataKuliah),
                submittedAt: new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }),
            },
            student: { ...p.student, accessStatus: 'MenungguSidang' },
            notifications: [
                {
                    id: Date.now(),
                    message: 'Dokumen sidang magang berhasil dikirim. Tim PPAIP akan meninjau dan menetapkan jadwal sidang Anda.',
                    time: 'Baru saja',
                },
                ...p.notifications,
            ],
        }));
    }, [setState]);

    const assignSidangSchedule = useCallback(() => {
        setState((p) => ({
            ...p,
            sidangSchedule: MOCK_SIDANG_SCHEDULE,
            notifications: [
                {
                    id: Date.now(),
                    message: `Jadwal sidang magang telah ditetapkan: ${MOCK_SIDANG_SCHEDULE.tanggal} pukul ${MOCK_SIDANG_SCHEDULE.waktu.split(' - ')[0]} di ${MOCK_SIDANG_SCHEDULE.ruangan}.`,
                    time: 'Baru saja',
                },
                ...p.notifications,
            ],
        }));
    }, [setState]);

    const completeSidangCycle = useCallback(() => {
        setState((p) => ({
            ...p,
            student: { ...p.student, accessStatus: 'SiklusSelesai' },
            notifications: [
                {
                    id: Date.now(),
                    message: 'Selamat! Siklus magang Anda telah berhasil diselesaikan. Terima kasih atas dedikasi Anda.',
                    time: 'Baru saja',
                },
                ...p.notifications,
            ],
        }));
    }, [setState]);

    const resetFullCycle = useCallback(() => {
        setState((p) => ({
            ...p,
            student: { ...p.student, accessStatus: 'Unverified', dpm: null, isIndependent: false, approvedLogbookCount: 0 },
            form1Submission: null,
            form2Submissions: [],
            pengajuanPembimbing: null,
            logbookEntries: [],
            sidangSubmission: null,
            sidangSchedule: null,
            activeApplications: [],
            notifications: [
                {
                    id: Date.now(),
                    message: 'Siklus magang telah direset oleh PPAIP. Anda dapat memulai siklus magang baru.',
                    time: 'Baru saja',
                },
            ],
        }));
    }, [setState]);

    return { submitSidang, assignSidangSchedule, completeSidangCycle, resetFullCycle };
}
