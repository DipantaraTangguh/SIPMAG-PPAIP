import { useState, useCallback, useMemo } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '../context/AppContext';
import { useForm1Workflow } from '../context/StudentWorkflowContext';

export default function useForm1() {
    const { student } = useAuth();
    const { submitForm1 } = useForm1Workflow();
    const navigate = useNavigate();
    const location = useLocation();
    const readOnlyFields = useMemo(
        () => ({
            nama: student?.name ?? '',
            nim: student?.nim ?? '',
            programStudi: student?.programStudi ?? '',
            semester: student?.semester ?? '',
            tahunAkademik: student?.tahunAkademik ?? '',
            jumlahSks: student?.jumlahSks ?? '',
            ipk: student?.ipk ?? '',
        }),
        [student?.name, student?.nim, student?.programStudi,
         student?.semester, student?.tahunAkademik, student?.jumlahSks, student?.ipk],
    );
    const prefill = location.state?.prefill;
    const [formData, setFormData] = useState({
        rencanaSkema: prefill?.rencanaSkema ?? '',
        topikTempat: prefill?.topikTempat ?? '',
        output: prefill?.output ?? '',
        declarationChecked: false,
    });

    const [errors, setErrors] = useState({});
    const [isSubmitting, setIsSubmitting] = useState(false);

    const updateField = useCallback((field, value) => {
        setFormData((prev) => ({ ...prev, [field]: value }));
        setErrors((prev) => {
            const next = { ...prev };
            delete next[field];
            return next;
        });
    }, []);

    const validateForm = useCallback(() => {
        const e = {};

        if (!formData.rencanaSkema) e.rencanaSkema = 'Pilih skema magang terlebih dahulu';
        if (!formData.topikTempat.trim()) e.topikTempat = 'Topik/tempat magang wajib diisi';
        if (!formData.output) e.output = 'Pilih output yang ditargetkan';
        if (!formData.declarationChecked) e.declarationChecked = true;

        setErrors(e);
        return Object.keys(e).length === 0;
    }, [formData]);

    const isFormValid = useMemo(
        () =>
            formData.rencanaSkema !== '' &&
            formData.topikTempat.trim() !== '' &&
            formData.output !== '' &&
            formData.declarationChecked,
        [formData],
    );

    const handleSubmit = useCallback(
        async (e) => {
            e.preventDefault();
            if (!validateForm()) return;

            setIsSubmitting(true);

            try {
                const fd = new FormData();
                fd.append('skemaMagang',  formData.rencanaSkema);
                fd.append('topikMagang',  formData.topikTempat);
                fd.append('outputTarget', formData.output);

                await submitForm1(fd);
                navigate('/form1/status', { replace: true });
            } catch (err) {
                console.error('[Form1] Submit error:', err);
                setErrors({ _general: err.message || 'Gagal mengirim Form 1' });
            } finally {
                setIsSubmitting(false);
            }
        },
        [formData, validateForm, navigate, submitForm1],
    );

    const handleCancel = useCallback(() => {
        navigate('/dashboard');
    }, [navigate]);

    return {
        readOnlyFields,
        formData,
        errors,
        isSubmitting,
        isFormValid,
        updateField,
        handleSubmit,
        handleCancel,
    };
}
