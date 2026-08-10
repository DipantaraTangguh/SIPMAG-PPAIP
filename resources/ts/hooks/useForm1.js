import { useState, useCallback, useMemo, useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '../context/AppContext';
import { useForm1Workflow } from '../context/StudentWorkflowContext';
import { api } from '../lib/api';

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
        jenisMagang: prefill?.jenisMagang ?? '',
        rencanaSkema: prefill?.rencanaSkema ?? '',
        topikTempat: prefill?.topikTempat ?? '',
        output: prefill?.output ?? '',
        catatanKhusus: prefill?.catatanKhusus ?? '',
        declarationChecked: false,
    });

    const [errors, setErrors] = useState({});
    const [isSubmitting, setIsSubmitting] = useState(false);

    // Kalau mahasiswa sudah pernah menyelesaikan magang wajib, opsi wajib dikunci.
    const [hasCompletedWajib, setHasCompletedWajib] = useState(false);
    // Syarat SKS minimal; angkanya ikut server supaya tidak ada dua sumber.
    const [sksRequirement, setSksRequirement] = useState({
        minSks: null,
        jumlahSks: null,
        meets: true,
    });
    useEffect(() => {
        let active = true;
        api.get('/form1')
            .then((res) => {
                if (!active) return;
                setHasCompletedWajib(Boolean(res.has_completed_wajib));
                setSksRequirement({
                    minSks: res.min_sks ?? null,
                    jumlahSks: res.jumlah_sks ?? null,
                    meets: res.meets_sks_requirement !== false,
                });
            })
            .catch(() => { /* biarkan default, backend tetap menolak */ });
        return () => { active = false; };
    }, []);

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

        if (!formData.jenisMagang) e.jenisMagang = 'Pilih jenis magang terlebih dahulu';
        if (formData.jenisMagang === 'wajib' && hasCompletedWajib) {
            e.jenisMagang = 'Anda sudah pernah menyelesaikan magang wajib. Silakan pilih magang non-wajib.';
        }
        if (!formData.rencanaSkema) e.rencanaSkema = 'Pilih skema magang terlebih dahulu';
        if (!formData.topikTempat.trim()) e.topikTempat = 'Topik/tempat magang wajib diisi';
        if (!formData.output) e.output = 'Pilih output yang ditargetkan';
        if (!formData.declarationChecked) e.declarationChecked = true;
        if (!sksRequirement.meets) {
            e.submit = `SKS Anda belum memenuhi syarat minimal ${sksRequirement.minSks} SKS untuk mengajukan Form 1.`;
        }

        setErrors(e);
        return Object.keys(e).length === 0;
    }, [formData, hasCompletedWajib, sksRequirement]);

    const isFormValid = useMemo(
        () =>
            formData.jenisMagang !== '' &&
            !(formData.jenisMagang === 'wajib' && hasCompletedWajib) &&
            formData.rencanaSkema !== '' &&
            formData.topikTempat.trim() !== '' &&
            formData.output !== '' &&
            formData.declarationChecked &&
            sksRequirement.meets,
        [formData, hasCompletedWajib, sksRequirement],
    );

    const handleSubmit = useCallback(
        async (e) => {
            e.preventDefault();
            if (!validateForm()) return;

            setIsSubmitting(true);

            try {
                const fd = new FormData();
                fd.append('jenisMagang',  formData.jenisMagang);
                fd.append('skemaMagang',  formData.rencanaSkema);
                fd.append('topikMagang',  formData.topikTempat);
                fd.append('outputTarget', formData.output);
                // Opsional: hanya dikirim kalau mahasiswa mengisinya.
                if (formData.catatanKhusus.trim()) {
                    fd.append('catatanKhusus', formData.catatanKhusus.trim());
                }

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
        hasCompletedWajib,
        sksRequirement,
        updateField,
        handleSubmit,
        handleCancel,
    };
}
