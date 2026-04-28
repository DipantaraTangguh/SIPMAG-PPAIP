/**
 * useVacancyDetail.js
 * Custom hook managing CV upload, apply logic, and simulation integration
 * for the vacancy detail page.
 *
 * @param {object} vacancy — The vacancy detail object.
 * @returns {object} State and handlers for the detail page.
 */
import { useState, useCallback, useMemo } from 'react';
import { useSimulation } from '../context/SimulationContext';
import { canAccessPortal } from '../utils/accessUtils';

export default function useVacancyDetail(vacancy) {
    const { student, activeApplications, applyToVacancy } = useSimulation();

    const [cvFile, setCvFile] = useState(null);
    const [cvError, setCvError] = useState(null);
    const [isApplying, setIsApplying] = useState(false);
    const [justApplied, setJustApplied] = useState(false);

    const accessStatus = student.accessStatus;
    const canApply = canAccessPortal(accessStatus);

    // Check if THIS specific vacancy was already applied to (persisted in context)
    const alreadyApplied = useMemo(() => {
        if (!vacancy || !activeApplications) return false;
        return activeApplications.some(
            (app) => app.vacancyId === vacancy.id
        );
    }, [vacancy, activeApplications]);

    // Check if the student has applied to ANY vacancy
    const hasAnyApplication = useMemo(() => {
        return (activeApplications || []).length > 0;
    }, [activeApplications]);

    // Combined: either just applied in this session, or was already applied
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

    const handleApply = useCallback(() => {
        if (!vacancy) return;

        setIsApplying(true);
        // Simulate 1000ms API delay
        setTimeout(() => {
            applyToVacancy(vacancy, cvFile);
            setIsApplying(false);
            setJustApplied(true);
        }, 1000);
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
