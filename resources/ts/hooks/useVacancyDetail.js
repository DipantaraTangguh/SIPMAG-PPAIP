import { useState, useCallback, useMemo } from 'react';
import { useAuth } from '../context/AppContext';
import { useApplicationWorkflow } from '../context/StudentWorkflowContext';
import {
    canAccessPortal,
    hasSecuredInternship,
    SECURED_INTERNSHIP_MESSAGE,
} from '../utils/accessUtils';

export default function useVacancyDetail(vacancy) {
    const { student } = useAuth();
    const { activeApplications, applyToVacancy } = useApplicationWorkflow();

    const [cvFile, setCvFile] = useState(null);
    const [cvError, setCvError] = useState(null);
    const [isApplying, setIsApplying] = useState(false);
    const [justApplied, setJustApplied] = useState(false);

    const accessStatus = student?.accessStatus;
    const internshipSecured = hasSecuredInternship(accessStatus);
    const canApply = canAccessPortal(accessStatus) && !internshipSecured;

    // Cek lowongan ini dulu, biar tombol nggak bisa apply dobel.
    const alreadyApplied = useMemo(() => {
        if (!vacancy || !activeApplications) return false;
        return activeApplications.some(
            (app) => app.vacancyId === vacancy.id
        );
    }, [vacancy, activeApplications]);

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
        if (!vacancy || !cvFile || internshipSecured) return;

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
    }, [vacancy, cvFile, internshipSecured, applyToVacancy]);

    return {
        cvFile,
        cvError,
        isApplying,
        isApplied,
        canApply,
        accessStatus,
        internshipSecured,
        securedInternshipMessage: SECURED_INTERNSHIP_MESSAGE,
        handleFileChange,
        handleRemoveFile,
        handleApply,
    };
}
