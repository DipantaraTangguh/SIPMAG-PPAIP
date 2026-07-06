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
        studentSignatureFile: null,
        output: prefill?.output ?? '',
        declarationChecked: false,
    });

    const [errors, setErrors] = useState({});
    const [signatureFileError, setSignatureFileError] = useState(null);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [isSignatureDragging, setIsSignatureDragging] = useState(false);
    const updateField = useCallback((field, value) => {
        setFormData((prev) => ({ ...prev, [field]: value }));
        setErrors((prev) => {
            const next = { ...prev };
            delete next[field];
            return next;
        });
    }, []);

    const handleSignatureFileChange = useCallback((file) => {
        if (!file) return;

        const allowedTypes = ['image/jpeg', 'image/png'];
        if (!allowedTypes.includes(file.type)) {
            setSignatureFileError('Format tanda tangan tidak didukung. Gunakan JPG atau PNG.');
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            setSignatureFileError('Ukuran file tanda tangan melebihi 5MB.');
            return;
        }

        setFormData((prev) => ({ ...prev, studentSignatureFile: file }));
        setSignatureFileError(null);
        setErrors((prev) => {
            const next = { ...prev };
            delete next.studentSignatureFile;
            return next;
        });
    }, []);

    const removeSignatureFile = useCallback(() => {
        setFormData((prev) => ({ ...prev, studentSignatureFile: null }));
    }, []);

    const validateForm = useCallback(() => {
        const e = {};

        if (!formData.rencanaSkema) e.rencanaSkema = 'Pilih skema magang terlebih dahulu';
        if (!formData.topikTempat.trim()) e.topikTempat = 'Topik/tempat magang wajib diisi';
        if (!formData.studentSignatureFile) e.studentSignatureFile = 'Tanda tangan mahasiswa wajib diunggah';
        if (!formData.output) e.output = 'Pilih output yang ditargetkan';
        if (!formData.declarationChecked) e.declarationChecked = true;

        setErrors(e);
        return Object.keys(e).length === 0;
    }, [formData]);
    const isFormValid = useMemo(
        () =>
            formData.rencanaSkema !== '' &&
            formData.topikTempat.trim() !== '' &&
            formData.studentSignatureFile !== null &&
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
                fd.append('studentSignature', formData.studentSignatureFile);

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
        signatureFileError,
        isSubmitting,
        isSignatureDragging,
        setIsSignatureDragging,
        isFormValid,
        updateField,
        handleSignatureFileChange,
        removeSignatureFile,
        handleSubmit,
        handleCancel,
    };
}
