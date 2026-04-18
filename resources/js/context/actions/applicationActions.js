/**
 * context/actions/applicationActions.js
 * Vacancy application actions.
 */
import { useCallback } from 'react';

export function useApplicationActions(setState) {
    const applyToVacancy = useCallback((vacancy, cvFile) => {
        const newApp = {
            id: Date.now(),
            companyName: vacancy.companyName,
            position: vacancy.position,
            status: 'Dilamar',
            statusColor: 'blue',
            appliedAt: new Date().toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric',
            }),
        };
        setState((p) => ({
            ...p,
            activeApplications: [newApp, ...(p.activeApplications || [])],
            student: {
                ...p.student,
                accessStatus: 'HasApplication',
                isIndependent: false,
            },
            notifications: [
                {
                    id: Date.now(),
                    message: `Lamaran ke ${vacancy.companyName} berhasil dikirim. Selanjutnya ajukan Dosen Pembimbing Magang.`,
                    time: 'Baru saja',
                },
                ...p.notifications,
            ],
        }));
    }, [setState]);

    return { applyToVacancy };
}
