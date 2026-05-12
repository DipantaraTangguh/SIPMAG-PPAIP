/**
 * useForm1.js
 * Custom hook managing all state, validation, and submission logic
 * for the Form Magang-01 (Surat Keterangan Memenuhi Syarat Akademik).
 * Uses SimulationContext for student data and form submission.
 *
 * @returns {object} Form state, handlers, read-only fields, and validation helpers.
 */
import { useState, useCallback, useMemo } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { useSimulation } from '../context/SimulationContext';

export default function useForm1() {
    const { student, submitForm1 } = useSimulation();
    const navigate = useNavigate();
    const location = useLocation();

    /* ── Read-only values from student profile ───── */
    const readOnlyFields = useMemo(
        () => ({
            nama: student?.name ?? '',
            nim: student?.nim ?? '',
            programStudi: student?.programStudi ?? '',
        }),
        [student?.name, student?.nim, student?.programStudi],
    );

    /* ── Pre-fill from rejected resubmit (React Router state) ── */
    const prefill = location.state?.prefill;

    /* ── Editable form state ─────────────────────── */
    const [formData, setFormData] = useState({
        semester: prefill?.semester ?? '',
        tahunAkademik: prefill?.tahunAkademik ?? '',
        jumlahSks: prefill?.jumlahSks ?? '',
        ipk: prefill?.ipk ?? '',
        rencanaSkema: prefill?.rencanaSkema ?? '',
        topikTempat: prefill?.topikTempat ?? '',
        transkripFile: null,
        output: prefill?.output ?? '',
        declarationChecked: false,
    });

    const [errors, setErrors] = useState({});
    const [fileError, setFileError] = useState(null);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [isDragging, setIsDragging] = useState(false);

    /* ── Field updater (clears per-field error) ──── */
    const updateField = useCallback((field, value) => {
        setFormData((prev) => ({ ...prev, [field]: value }));
        setErrors((prev) => {
            const next = { ...prev };
            delete next[field];
            return next;
        });
    }, []);

    /* ── File handling ───────────────────────────── */
    const handleFileChange = useCallback((file) => {
        if (!file) return;

        const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        if (!allowedTypes.includes(file.type)) {
            setFileError('Format file tidak didukung. Gunakan PDF, JPG, atau PNG.');
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            setFileError('Ukuran file melebihi 5MB.');
            return;
        }

        setFormData((prev) => ({ ...prev, transkripFile: file }));
        setFileError(null);
        setErrors((prev) => {
            const next = { ...prev };
            delete next.transkripFile;
            return next;
        });
    }, []);

    const removeFile = useCallback(() => {
        setFormData((prev) => ({ ...prev, transkripFile: null }));
    }, []);

    /* ── Validation ──────────────────────────────── */
    const validateForm = useCallback(() => {
        const e = {};

        if (!formData.semester.trim()) e.semester = 'Semester wajib diisi';
        if (!formData.tahunAkademik.trim()) e.tahunAkademik = 'Tahun akademik wajib diisi';

        if (!formData.jumlahSks.trim()) {
            e.jumlahSks = 'Jumlah SKS wajib diisi';
        } else if (isNaN(Number(formData.jumlahSks))) {
            e.jumlahSks = 'Jumlah SKS harus berupa angka';
        }

        if (!formData.ipk.trim()) {
            e.ipk = 'IPK wajib diisi';
        } else if (!/^\d+(\.\d+)?$/.test(formData.ipk.trim())) {
            e.ipk = 'IPK harus berupa angka desimal (contoh: 3.75)';
        }

        if (!formData.rencanaSkema) e.rencanaSkema = 'Pilih skema magang terlebih dahulu';
        if (!formData.topikTempat.trim()) e.topikTempat = 'Topik/tempat magang wajib diisi';
        // File upload optional in simulation mode
        // if (!formData.transkripFile) e.transkripFile = 'Transkrip nilai wajib diunggah';
        if (!formData.output) e.output = 'Pilih output yang ditargetkan';
        if (!formData.declarationChecked) e.declarationChecked = true;

        setErrors(e);
        return Object.keys(e).length === 0;
    }, [formData]);

    /* ── Derived: is every required field filled? ── */
    const isFormValid = useMemo(
        () =>
            formData.semester.trim() !== '' &&
            formData.tahunAkademik.trim() !== '' &&
            formData.jumlahSks.trim() !== '' &&
            formData.ipk.trim() !== '' &&
            formData.rencanaSkema !== '' &&
            formData.topikTempat.trim() !== '' &&
            // formData.transkripFile !== null && // optional in simulation
            formData.output !== '' &&
            formData.declarationChecked,
        [formData],
    );

    /* ── Submit ──────────────────────────────────── */
    const handleSubmit = useCallback(
        async (e) => {
            e.preventDefault();
            if (!validateForm()) return;

            setIsSubmitting(true);

            try {
                // Map frontend field names → Laravel API field names
                const apiPayload = {
                    semester:     formData.semester,
                    jumlahSKS:    formData.jumlahSks,
                    ipk:          formData.ipk,
                    skemaMagang:  formData.rencanaSkema,
                    topikMagang:  formData.topikTempat,
                    outputTarget: formData.output,
                };

                await submitForm1(apiPayload);
                navigate('/form1/status', { replace: true });
            } catch (err) {
                console.error('[Form1] Submit error:', err);
                setErrors({ _general: err.message || 'Gagal mengirim Form 1' });
            } finally {
                setIsSubmitting(false);
            }
        },
        [formData, readOnlyFields, validateForm, navigate, submitForm1],
    );

    /* ── Cancel ──────────────────────────────────── */
    const handleCancel = useCallback(() => {
        navigate('/dashboard');
    }, [navigate]);

    return {
        readOnlyFields,
        formData,
        errors,
        fileError,
        isSubmitting,
        isDragging,
        setIsDragging,
        isFormValid,
        updateField,
        handleFileChange,
        removeFile,
        handleSubmit,
        handleCancel,
    };
}
