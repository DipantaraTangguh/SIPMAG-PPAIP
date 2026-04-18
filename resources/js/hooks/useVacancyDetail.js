/**
 * useVacancyDetail.js
 * Custom hook managing CV upload, apply logic, and simulation integration
 * for the vacancy detail page.
 *
 * @param {object} vacancy — The vacancy detail object.
 * @returns {object} State and handlers for the detail page.
 */
import { useState, useCallback } from 'react';
import { useSimulation } from '../context/SimulationContext';
import { canAccessPortal } from '../utils/accessUtils';

export default function useVacancyDetail(vacancy) {
    const { student, applyToVacancy } = useSimulation();

    const [cvFile, setCvFile] = useState(null);
    const [cvError, setCvError] = useState(null);
    const [isApplying, setIsApplying] = useState(false);
    const [isApplied, setIsApplied] = useState(false);

    const accessStatus = student.accessStatus;
    const canApply = canAccessPortal(accessStatus);

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
            setIsApplied(true);
        }, 1000);
    }, [vacancy, cvFile, applyToVacancy]);

    return {
        cvFile,
        cvError,
        isApplying,
        isApplied,
        canApply,
        accessStatus,
        handleFileChange,
        handleRemoveFile,
        handleApply,
    };
}
