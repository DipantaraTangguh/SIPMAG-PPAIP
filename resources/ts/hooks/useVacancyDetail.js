import { useState, useCallback, useMemo } from 'react';
import { useAuth } from '../context/AppContext';
import { useApplicationWorkflow } from '../context/StudentWorkflowContext';
import { canAccessPortal } from '../utils/accessUtils';

export default function useVacancyDetail(vacancy) {
    const { student } = useAuth();
    const { activeApplications, applyToVacancy } = useApplicationWorkflow();

    const [cvFile, setCvFile] = useState(null);
    const [cvError, setCvError] = useState(null);
    const [isApplying, setIsApplying] = useState(false);
    const [justApplied, setJustApplied] = useState(false);

    const accessStatus = student?.accessStatus;
    const canApply = canAccessPortal(accessStatus);

    // Cek lowongan ini dulu, biar tombol nggak bisa apply dobel.
    const alreadyApplied = useMemo(() => {
        if (!vacancy || !activeApplications) return false;
        return activeApplications.some(
            (app) => app.vacancyId === vacancy.id
        );
    }, [vacancy, activeApplications]);

    // Portal mitra cuma boleh satu lamaran aktif.
    const hasAnyApplication = useMemo(() => {
        return (activeApplications || []).length > 0;
    }, [activeApplications]);

    // Gabung state session dan context supaya UI tetap konsisten.
    const isApplied = justApplied || alreadyApplied;

    const handleFileChange = useCallback((file) => {
        if (!file) return;

        if (file.type !== 'application/pdf') {
            setCvError('Hanya file PDF yang diizinkan.');
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            setCvError('Ukuran file melebihi 5MB.');
            return;
        }

        setCvFile(file);
        setCvError(null);
    }, []);

    const handleRemoveFile = useCallback(() => {
        setCvFile(null);
        setCvError(null);
    }, []);

    const handleApply = useCallback(async () => {
        if (!vacancy || !cvFile) return;

        setIsApplying(true);
        try {
            await applyToVacancy(vacancy.id, cvFile);
            setJustApplied(true);
        } catch (err) {
            console.error('[Apply] Error:', err);
            setCvError(err.message || 'Gagal melamar.');
        } finally {
            setIsApplying(false);
        }
    }, [vacancy, cvFile, applyToVacancy]);

    return {
        cvFile,
        cvError,
        isApplying,
        isApplied,
        canApply,
        accessStatus,
        hasAnyApplication,
        handleFileChange,
        handleRemoveFile,
        handleApply,
    };
}
