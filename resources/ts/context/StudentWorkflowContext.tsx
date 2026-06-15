import React, { createContext, useContext, useState, useMemo, useCallback, useEffect, useRef } from 'react';
import { api } from '../lib/api';
import { useAuth } from './AppContext';
import { mapForm2Submission } from './simulationMappers';

const StudentWorkflowContext = createContext<any>(null);
const Form1Context = createContext<any>(null);
const ApplicationContext = createContext<any>(null);
const Form2Context = createContext<any>(null);
const GuidanceContext = createContext<any>(null);
const LogbookContext = createContext<any>(null);
const DefenseContext = createContext<any>(null);
const WorkflowNotificationsContext = createContext<any>(null);

const EMPTY_STATE = {
    form1Submission: null,
    form2Submissions: [],
    pengajuanPembimbing: null,
    logbookEntries: [],
    logbookPeriod: null,
    sidangSubmission: null,
    sidangSchedule: null,
    activeApplications: [],
    notifications: [],
};

export function StudentWorkflowProvider({ children }) {
    const [state, setState] = useState(EMPTY_STATE);
    const fetchRunRef = useRef(0);
    const {
        isLoading,
        isLoggedIn,
        userRole,
        student,
        refreshProfile,
        updateStudentLocally,
    } = useAuth();
    const fetchAllStudentData = useCallback(async () => {
        const fetchRun = ++fetchRunRef.current;

        try {
            const [form1Res, appsRes, form2Res, logbookRes, sidangRes, supervisorRes] = await Promise.allSettled([
                api.get('/form1'),
                api.get('/applications'),
                api.get('/form2'),
                api.get('/logbooks'),
                api.get('/defense'),
                api.get('/supervisor-application'),
            ]);

            if (fetchRun !== fetchRunRef.current) {
                return;
            }

            setState((s) => ({
                ...s,
                form1Submission: form1Res.status === 'fulfilled' && form1Res.value.form1
                    ? {
                        ...form1Res.value.form1,
                        status: form1Res.value.access_status,
                        rejectionReason: form1Res.value.rejection_reason,
                        pdfPath: form1Res.value.pdf_path,
                        approver: form1Res.value.approver || null,
                        submittedAt: form1Res.value.submitted_at
                            ? new Date(form1Res.value.submitted_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
                            : null,
                    }
                    : null,
                activeApplications: appsRes.status === 'fulfilled'
                    ? (appsRes.value.applications?.data ?? appsRes.value.applications ?? []).map(a => ({
                        id: a.id,
                        vacancyId: a.internship_id,
                        companyName: a.internship?.company_name,
                        position: a.internship?.position,
                        status: a.status,
                        appliedAt: a.created_at,
                    }))
                    : [],
                form2Submissions: form2Res.status === 'fulfilled'
                    ? (form2Res.value.submissions?.data ?? form2Res.value.submissions ?? []).map(mapForm2Submission)
                    : [],
                logbookEntries: logbookRes.status === 'fulfilled'
                    ? (logbookRes.value.logbooks?.data ?? logbookRes.value.logbooks ?? []).map(l => ({
                        id: l.id,
                        tanggal: l.tanggal,
                        kegiatanHarian: l.kegiatan_harian,
                        hasil: l.hasil,
                        status: { Approved: 'Disetujui', PendingReview: 'Menunggu Review', Rejected: 'Ditolak' }[l.status] || l.status,
                        dpmNote: l.dpm_note,
                    }))
                    : [],
                logbookPeriod: logbookRes.status === 'fulfilled'
                    ? logbookRes.value.internship_period
                    : null,
                sidangSubmission: sidangRes.status === 'fulfilled' && sidangRes.value.submission
                    ? {
                        id: sidangRes.value.submission.id,
                        status: sidangRes.value.submission.status,
                        submittedAt: sidangRes.value.submission.submitted_at,
                        laporanPath: sidangRes.value.submission.laporan_path,
                        posterPath: sidangRes.value.submission.poster_path,
                        fotoKegiatan1Path: sidangRes.value.submission.foto_kegiatan_1_path,
                        fotoKegiatan2Path: sidangRes.value.submission.foto_kegiatan_2_path,
                        krsPath: sidangRes.value.submission.krs_path,
                        scheduledDate: sidangRes.value.submission.scheduled_date,
                        scheduledTime: sidangRes.value.submission.scheduled_time,
                        room: sidangRes.value.submission.room,
                        dosenPenguji1: sidangRes.value.submission.dosen_penguji_1,
                        dosenPenguji2: sidangRes.value.submission.dosen_penguji_2,
                    }
                    : null,
                sidangSchedule: sidangRes.status === 'fulfilled'
                    && sidangRes.value.submission
                    && sidangRes.value.submission.status === 'Scheduled'
                    ? {
                        tanggal: sidangRes.value.submission.scheduled_date
                            ? new Date(sidangRes.value.submission.scheduled_date).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
                            : 'Belum ditetapkan',
                        waktu: sidangRes.value.submission.scheduled_time || '-',
                        ruangan: sidangRes.value.submission.room || '-',
                        dosenPenguji1: sidangRes.value.submission.dosen_penguji_1 || '-',
                        dosenPenguji2: sidangRes.value.submission.dosen_penguji_2 || '-',
                    }
                    : null,
                pengajuanPembimbing: supervisorRes.status === 'fulfilled' && supervisorRes.value.application
                    ? {
                        namaPerusahaan: supervisorRes.value.application.company_name,
                        namaPraktisi: supervisorRes.value.application.nama_praktisi || (supervisorRes.value.application.company_contact ? supervisorRes.value.application.company_contact.split(' - ')[0] : ''),
                        jabatanPraktisi: supervisorRes.value.application.jabatan_praktisi || '',
                        noTelepon: supervisorRes.value.application.no_telepon || (supervisorRes.value.application.company_contact ? supervisorRes.value.application.company_contact.split(' - ')[1] : ''),
                        email: supervisorRes.value.application.email || '',
                        mulaiMagang: supervisorRes.value.application.mulai_magang ? new Date(supervisorRes.value.application.mulai_magang).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '',
                        selesaiMagang: supervisorRes.value.application.selesai_magang ? new Date(supervisorRes.value.application.selesai_magang).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '',
                        submittedAt: supervisorRes.value.application.submitted_at
                            ? new Date(supervisorRes.value.application.submitted_at).toLocaleDateString('id-ID', {
                                day: 'numeric', month: 'short', year: 'numeric',
                            })
                            : null,
                        loaPath: supervisorRes.value.application.loa_path,
                    }
                    : null,
            }));
        } catch { /* biarin lanjut walau sebagian gagal */ }
    }, []);

    useEffect(() => {
        if (isLoading) {
            return;
        }

        if (!isLoggedIn) {
            fetchRunRef.current += 1;
            setState(EMPTY_STATE);
            return;
        }

        if (userRole === 'mahasiswa' && student?.id) {
            fetchAllStudentData();
        }
    }, [fetchAllStudentData, isLoading, isLoggedIn, student?.id, userRole]);

    const submitForm1 = useCallback(async (formData) => {
        try {
            await api.upload('/form1', formData);
            await refreshProfile();
            updateStudentLocally({ accessStatus: 'PendingReview' });
            const form1Res = await api.get('/form1');
            setState((s) => ({
                ...s,
                form1Submission: {
                    ...form1Res.form1,
                    status: form1Res.access_status,
                    rejectionReason: form1Res.rejection_reason,
                    pdfPath: form1Res.pdf_path,
                    approver: form1Res.approver || null,
                    submittedAt: form1Res.submitted_at
                        ? new Date(form1Res.submitted_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
                        : null,
                },
                notifications: [
                    { id: Date.now(), message: 'Form 1 berhasil diajukan. Menunggu persetujuan Kaprodi.', time: 'Baru saja' },
                    ...s.notifications,
                ],
            }));
        } catch (err) {
            throw err;
        }
    }, [refreshProfile, updateStudentLocally]);

    const resetForm1 = useCallback(async () => {
        await refreshProfile();
        const form1Res = await api.get('/form1');
        setState((s) => ({
            ...s,
            form1Submission: form1Res.form1 ? {
                ...form1Res.form1,
                status: form1Res.access_status,
                rejectionReason: form1Res.rejection_reason,
                approver: form1Res.approver || null,
            } : null,
        }));
    }, [refreshProfile]);
    const applyToVacancy = useCallback(async (vacancyId, cvFile) => {
        const formData = new FormData();
        formData.append('internship_id', vacancyId);
        formData.append('cv_file', cvFile);

        const data = await api.upload('/applications', formData);
        const app = data.application;

        updateStudentLocally({ accessStatus: 'HasApplication' });
        setState((s) => ({
            ...s,
            activeApplications: [
                ...s.activeApplications,
                {
                    id: app.id,
                    vacancyId: app.internship_id,
                    companyName: app.internship?.company_name,
                    position: app.internship?.position,
                    status: app.status,
                    appliedAt: app.created_at,
                },
            ],
        }));
        await refreshProfile();
    }, [refreshProfile, updateStudentLocally]);
    const submitForm2 = useCallback(async (formData) => {
        const payload = {
            company_name:      formData.namaPerusahaan,
            alamat_perusahaan: formData.alamatPerusahaan,
            lingkup_magang:    formData.lingkupMagang,
            tanggal_mulai:     formData.tanggalMulai,
            tanggal_selesai:   formData.tanggalSelesai,
        };
        const data = await api.post('/form2', payload);
        setState((s) => ({
            ...s,
            form2Submissions: [mapForm2Submission(data.submission), ...s.form2Submissions],
            notifications: [
                { id: Date.now(), message: 'Form 2 berhasil diajukan. Menunggu review PPAIP.', time: 'Baru saja' },
                ...s.notifications,
            ],
        }));
    }, []);
    const submitPengajuanPembimbing = useCallback(async (formData) => {
        const fd = new FormData();
        fd.append('company_name', formData.namaPerusahaan);
        fd.append('company_contact', `${formData.namaPraktisi} - ${formData.noTelepon}`);
        fd.append('nama_praktisi', formData.namaPraktisi);
        fd.append('jabatan_praktisi', formData.jabatanPraktisi);
        fd.append('no_telepon', formData.noTelepon);
        fd.append('email', formData.email);
        fd.append('mulai_magang', formData.mulaiMagang);
        fd.append('selesai_magang', formData.selesaiMagang);
        if (formData.loaFile) {
            fd.append('loa_file', formData.loaFile);
        }

        await api.upload('/supervisor-application', fd);

        setState((s) => ({
            ...s,
            pengajuanPembimbing: {
                namaPerusahaan: formData.namaPerusahaan,
                namaPraktisi: formData.namaPraktisi,
                jabatanPraktisi: formData.jabatanPraktisi,
                noTelepon: formData.noTelepon,
                email: formData.email,
                mulaiMagang: formData.mulaiMagang ? new Date(formData.mulaiMagang).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '',
                selesaiMagang: formData.selesaiMagang ? new Date(formData.selesaiMagang).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '',
                submittedAt: new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }),
            },
            notifications: [
                { id: Date.now(), message: 'Pengajuan Pembimbing Magang berhasil dikirim.', time: 'Baru saja' },
                ...s.notifications,
            ],
        }));

        await refreshProfile();
        await fetchAllStudentData();
    }, [refreshProfile, fetchAllStudentData]);
    const addLogbookEntry = useCallback(async (entry) => {
        const data = await api.post('/logbooks', {
            tanggal: entry.tanggal,
            kegiatan_harian: entry.kegiatanHarian,
            hasil: entry.hasil,
        });

        const newEntry = {
            id: data.logbook.id,
            tanggal: data.logbook.tanggal,
            kegiatanHarian: data.logbook.kegiatan_harian,
            hasil: data.logbook.hasil,
            status: { Approved: 'Disetujui', PendingReview: 'Menunggu Review', Rejected: 'Ditolak' }[data.logbook.status] || data.logbook.status,
            dpmNote: null,
        };

        setState((s) => ({
            ...s,
            logbookEntries: [newEntry, ...s.logbookEntries],
            notifications: [
                { id: Date.now(), message: 'Logbook baru berhasil disimpan.', time: 'Baru saja' },
                ...s.notifications,
            ],
        }));
    }, []);

    const updateLogbookEntry = useCallback(async (entryId, updates) => {
        const data = await api.put(`/logbooks/${entryId}`, {
            tanggal: updates.tanggal,
            kegiatan_harian: updates.kegiatanHarian,
            hasil: updates.hasil,
        });

        setState((s) => ({
            ...s,
            logbookEntries: s.logbookEntries.map(e =>
                e.id === entryId ? {
                    ...e,
                    tanggal: data.logbook.tanggal,
                    kegiatanHarian: data.logbook.kegiatan_harian,
                    hasil: data.logbook.hasil,
                    status: 'Menunggu Review',
                    dpmNote: null,
                } : e
            ),
        }));
    }, []);
    const submitSidang = useCallback(async (files) => {
        const fd = new FormData();
        // Frontend pakai camelCase, backend minta field Laravel-style.
        fd.append('laporan', files.laporanAkhir || files.laporan);
        fd.append('poster', files.posterPresentasi || files.poster);
        fd.append('foto_kegiatan_1', files.fotoKegiatan1);
        fd.append('foto_kegiatan_2', files.fotoKegiatan2);
        fd.append('krs', files.krsMataKuliah || files.krs);

        await api.upload('/defense', fd);
        await refreshProfile();
        updateStudentLocally({ accessStatus: 'MenungguSidang' });

        setState((s) => ({
            ...s,
            sidangSubmission: { status: 'Pending', submittedAt: new Date().toISOString() },
            notifications: [
                { id: Date.now(), message: 'Dokumen sidang berhasil dikirim.', time: 'Baru saja' },
                ...s.notifications,
            ],
        }));
    }, [refreshProfile, updateStudentLocally]);

    const notifications = useMemo(() => {
        const list = [];

        // Form 1 jadi sumber status akademik awal.
        if (state.form1Submission) {
            const dateStr = state.form1Submission.submittedAt ? ` pada ${state.form1Submission.submittedAt}` : '';
            if (student?.accessStatus === 'PendingReview') {
                list.push({
                    message: `Form 1 Anda berhasil diajukan${dateStr}. Menunggu persetujuan Kaprodi.`,
                    time: state.form1Submission.submittedAt || 'Baru saja',
                });
            } else if (student?.accessStatus === 'RejectedForm1') {
                list.push({
                    message: `Form 1 Anda ditolak/perlu direvisi oleh Kaprodi${state.form1Submission.rejectionReason ? `: "${state.form1Submission.rejectionReason}"` : '.'}`,
                    time: 'Baru saja',
                });
            } else if (
                student?.accessStatus !== 'Unverified' &&
                student?.accessStatus !== 'RejectedForm1' &&
                student?.accessStatus !== 'PendingReview'
            ) {
                list.push({
                    message: 'Form 1 Anda telah disetujui oleh Kaprodi. Surat keterangan siap diunduh.',
                    time: state.form1Submission.submittedAt || 'Beberapa hari lalu',
                });
            }
        }

        // Lamaran mitra ikut nentuin akses ke DPM.
        if (state.activeApplications && state.activeApplications.length > 0) {
            state.activeApplications.forEach((app) => {
                const dateStr = app.appliedAt ? new Date(app.appliedAt).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }) : '';
                if (app.status === 'Diterima' || app.status === 'Accepted') {
                    list.push({
                        message: `Selamat! Lamaran Anda sebagai ${app.position} di ${app.companyName} telah Diterima.`,
                        time: dateStr || 'Baru saja',
                    });
                } else if (app.status === 'Ditolak' || app.status === 'Rejected') {
                    list.push({
                        message: `Lamaran Anda sebagai ${app.position} di ${app.companyName} ditolak.`,
                        time: dateStr || 'Baru saja',
                    });
                } else {
                    list.push({
                        message: `Lamaran Anda sebagai ${app.position} di ${app.companyName} berhasil dikirim.`,
                        time: dateStr || 'Baru saja',
                    });
                }
            });
        }

        // Jalur mandiri pakai Form 2 sebagai pengganti lamaran mitra.
        if (state.form2Submissions && state.form2Submissions.length > 0) {
            state.form2Submissions.forEach((sub) => {
                const statusMap = {
                    'Menunggu Review': 'sedang ditinjau.',
                    'Disetujui': 'telah disetujui oleh PPAIP.',
                    'Ditolak': `ditolak oleh PPAIP${sub.rejectionReason ? `: "${sub.rejectionReason}"` : '.'}`,
                };
                list.push({
                    message: `Pengajuan Magang Mandiri di ${sub.companyName} sebagai ${sub.position} ${statusMap[sub.status] || sub.status}`,
                    time: sub.submittedAt || 'Baru saja',
                });
            });
        }

        // Bagian bimbingan mulai setelah perusahaan/praktisi valid.
        if (state.pengajuanPembimbing) {
            if (student?.dpm) {
                list.push({
                    message: `Dosen Pembimbing Magang (DPM) Anda telah ditetapkan: ${student.dpm.name}.`,
                    time: state.pengajuanPembimbing.submittedAt || 'Baru saja',
                });
            } else {
                list.push({
                    message: `Pengajuan Pembimbing Magang di ${state.pengajuanPembimbing.namaPerusahaan} berhasil dikirim. Menunggu penetapan DPM.`,
                    time: state.pengajuanPembimbing.submittedAt || 'Baru saja',
                });
            }
        }

        // Logbook jadi progress utama sebelum sidang.
        if (state.logbookEntries && state.logbookEntries.length > 0) {
            const rejected = state.logbookEntries.filter(e => e.status === 'Ditolak' || e.status === 'Rejected');
            if (rejected.length > 0) {
                list.push({
                    message: `Terdapat ${rejected.length} entri logbook yang ditolak/perlu revisi oleh DPM.`,
                    time: 'Baru saja',
                });
            }

            const approvedCount = state.logbookEntries.filter(e => e.status === 'Disetujui' || e.status === 'Approved').length;
            if (approvedCount >= 6) {
                list.push({
                    message: 'Logbook magang lengkap (6/6 disetujui). Silakan mengajukan Sidang Magang.',
                    time: 'Baru saja',
                });
            }
        }

        // Sidang adalah state akhir cycle magang.
        if (state.sidangSubmission) {
            if (state.sidangSubmission.status === 'Scheduled' || state.sidangSchedule) {
                list.push({
                    message: `Jadwal Sidang Magang Anda telah ditetapkan pada ${state.sidangSchedule?.tanggal || state.sidangSubmission.scheduledDate} pukul ${state.sidangSchedule?.waktu || state.sidangSubmission.scheduledTime} di ${state.sidangSchedule?.ruangan || state.sidangSubmission.room}.`,
                    time: 'Baru saja',
                });
            } else if (state.sidangSubmission.status === 'Pending') {
                list.push({
                    message: 'Dokumen sidang magang berhasil dikirim. Menunggu verifikasi dokumen dan penjadwalan oleh Kaprodi.',
                    time: 'Baru saja',
                });
            }
        }

        if (student?.accessStatus === 'SiklusSelesai') {
            list.push({
                message: 'Selamat! Siklus magang Anda telah selesai. Terima kasih atas dedikasi Anda.',
                time: 'Baru saja',
            });
        }

        // Notifikasi terbaru lebih enak dibaca di paling atas.
        return list.reverse();
    }, [
        state.form1Submission,
        state.activeApplications,
        state.form2Submissions,
        state.logbookEntries,
        state.sidangSubmission,
        state.sidangSchedule,
        state.pengajuanPembimbing,
        student
    ]);
    const form1Value = useMemo(() => ({
        form1Submission: state.form1Submission,
        submitForm1,
        resetForm1,
    }), [state.form1Submission, submitForm1, resetForm1]);

    const applicationValue = useMemo(() => ({
        activeApplications: state.activeApplications,
        applyToVacancy,
    }), [state.activeApplications, applyToVacancy]);

    const form2Value = useMemo(() => ({
        form2Submissions: state.form2Submissions,
        submitForm2,
    }), [state.form2Submissions, submitForm2]);

    const guidanceValue = useMemo(() => ({
        pengajuanPembimbing: state.pengajuanPembimbing,
        submitPengajuanPembimbing,
    }), [state.pengajuanPembimbing, submitPengajuanPembimbing]);

    const logbookValue = useMemo(() => ({
        logbookEntries: state.logbookEntries,
        logbookPeriod: state.logbookPeriod,
        addLogbookEntry,
        updateLogbookEntry,
    }), [
        state.logbookEntries,
        state.logbookPeriod,
        addLogbookEntry,
        updateLogbookEntry,
    ]);

    const defenseValue = useMemo(() => ({
        sidangSubmission: state.sidangSubmission,
        sidangSchedule: state.sidangSchedule,
        submitSidang,
    }), [state.sidangSubmission, state.sidangSchedule, submitSidang]);

    const notificationValue = useMemo(() => ({
        notifications,
    }), [notifications]);

    const value = useMemo(() => ({
        ...state,
        notifications,
        submitForm1,
        resetForm1,
        applyToVacancy,
        submitForm2,
        submitPengajuanPembimbing,
        addLogbookEntry,
        updateLogbookEntry,
        submitSidang,
        // Dipakai saat UI butuh narik ulang data tanpa reload page.
        refreshProfile,
        fetchAllStudentData,
    }), [
        state, notifications, submitForm1, resetForm1,
        applyToVacancy, submitForm2, submitPengajuanPembimbing,
        addLogbookEntry, updateLogbookEntry, submitSidang,
        refreshProfile, fetchAllStudentData,
    ]);

    return (
        <StudentWorkflowContext.Provider value={value}>
            <Form1Context.Provider value={form1Value}>
                <ApplicationContext.Provider value={applicationValue}>
                    <Form2Context.Provider value={form2Value}>
                        <GuidanceContext.Provider value={guidanceValue}>
                            <LogbookContext.Provider value={logbookValue}>
                                <DefenseContext.Provider value={defenseValue}>
                                    <WorkflowNotificationsContext.Provider value={notificationValue}>
                                        {children}
                                    </WorkflowNotificationsContext.Provider>
                                </DefenseContext.Provider>
                            </LogbookContext.Provider>
                        </GuidanceContext.Provider>
                    </Form2Context.Provider>
                </ApplicationContext.Provider>
            </Form1Context.Provider>
        </StudentWorkflowContext.Provider>
    );
}

function useRequiredContext(context: React.Context<any>, hookName: string) {
    const ctx = useContext(context);
    if (!ctx) throw new Error(`${hookName} must be used within StudentWorkflowProvider`);
    return ctx;
}

export function useStudentWorkflow() {
    return useRequiredContext(StudentWorkflowContext, 'useStudentWorkflow');
}

export function useForm1Workflow() {
    return useRequiredContext(Form1Context, 'useForm1Workflow');
}

export function useApplicationWorkflow() {
    return useRequiredContext(ApplicationContext, 'useApplicationWorkflow');
}

export function useForm2Workflow() {
    return useRequiredContext(Form2Context, 'useForm2Workflow');
}

export function useGuidanceWorkflow() {
    return useRequiredContext(GuidanceContext, 'useGuidanceWorkflow');
}

export function useLogbookWorkflow() {
    return useRequiredContext(LogbookContext, 'useLogbookWorkflow');
}

export function useDefenseWorkflow() {
    return useRequiredContext(DefenseContext, 'useDefenseWorkflow');
}

export function useWorkflowNotifications() {
    return useRequiredContext(WorkflowNotificationsContext, 'useWorkflowNotifications');
}
