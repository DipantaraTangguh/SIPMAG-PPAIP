/**
 * context/actions/logbookActions.js
 * Logbook CRUD and simulation actions.
 */
import { useCallback } from 'react';
import {
    MOCK_LOGBOOK_ACTIVITIES,
    MOCK_LOGBOOK_DAYS,
    MOCK_LOGBOOK_DATES,
} from '../../constants/simulationConstants';

export function useLogbookActions(setState) {
    const addLogbookEntry = useCallback((entryData) => {
        const newEntry = {
            id: Date.now(),
            tanggal: entryData.tanggal || new Date().toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short',
                year: 'numeric',
            }),
            hari: new Date().toLocaleDateString('id-ID', { weekday: 'long' }),
            kegiatanHarian: entryData.kegiatanHarian,
            kegiatanDetail: entryData.kegiatanHarian,
            hasil: entryData.hasil,
            status: 'Menunggu Review',
        };
        setState((p) => ({
            ...p,
            logbookEntries: [...p.logbookEntries, newEntry],
            notifications: [
                {
                    id: Date.now(),
                    message: `Entri logbook "${entryData.kegiatanHarian.substring(0, 30)}..." berhasil ditambahkan.`,
                    time: 'Baru saja',
                },
                ...p.notifications,
            ],
        }));
    }, [setState]);

    const updateLogbookEntry = useCallback((entryId, updates) => {
        setState((p) => ({
            ...p,
            logbookEntries: p.logbookEntries.map((e) =>
                e.id === entryId
                    ? {
                          ...e,
                          ...updates,
                          kegiatanDetail: updates.kegiatanHarian || e.kegiatanDetail,
                          status: 'Menunggu Review',
                      }
                    : e
            ),
        }));
    }, [setState]);

    const approveLogbookEntry = useCallback((entryId) => {
        setState((p) => {
            const updatedEntries = p.logbookEntries.map((e) =>
                e.id === entryId ? { ...e, status: 'Disetujui' } : e
            );
            const newApprovedCount = updatedEntries.filter((e) => e.status === 'Disetujui').length;
            const isComplete = newApprovedCount >= 6;

            return {
                ...p,
                logbookEntries: updatedEntries,
                student: {
                    ...p.student,
                    approvedLogbookCount: newApprovedCount,
                    accessStatus: isComplete ? 'LogbookComplete' : p.student.accessStatus,
                },
                notifications: [
                    {
                        id: Date.now(),
                        message: isComplete
                            ? `🎓 6/6 logbook telah disetujui! Silakan daftar sidang magang.`
                            : `Entri logbook disetujui. Progress: ${newApprovedCount}/6.`,
                        time: 'Baru saja',
                    },
                    ...p.notifications,
                ],
            };
        });
    }, [setState]);

    const rejectLogbookEntry = useCallback((entryId) => {
        setState((p) => ({
            ...p,
            logbookEntries: p.logbookEntries.map((e) =>
                e.id === entryId ? { ...e, status: 'Ditolak' } : e
            ),
            notifications: [
                {
                    id: Date.now(),
                    message: 'Entri logbook ditolak oleh DPM. Silakan perbaiki dan kirim ulang.',
                    time: 'Baru saja',
                },
                ...p.notifications,
            ],
        }));
    }, [setState]);

    const completeSixLogbooks = useCallback(() => {
        const entries = MOCK_LOGBOOK_ACTIVITIES.map((activity, index) => ({
            id: Date.now() + index,
            tanggal: MOCK_LOGBOOK_DATES[index],
            hari: MOCK_LOGBOOK_DAYS[index],
            kegiatanHarian: activity.title,
            kegiatanDetail: activity.title,
            hasil: activity.hasil,
            status: 'Disetujui',
        }));

        setState((p) => ({
            ...p,
            logbookEntries: entries,
            student: {
                ...p.student,
                approvedLogbookCount: 6,
                accessStatus: 'LogbookComplete',
            },
            notifications: [
                {
                    id: Date.now(),
                    message: '🎓 6/6 logbook telah disetujui! Silakan daftar sidang magang.',
                    time: 'Baru saja',
                },
                ...p.notifications,
            ],
        }));
    }, [setState]);

    return { addLogbookEntry, updateLogbookEntry, approveLogbookEntry, rejectLogbookEntry, completeSixLogbooks };
}
