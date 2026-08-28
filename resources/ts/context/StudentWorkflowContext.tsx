import React, { createContext, useContext, useState, useMemo, useCallback, useEffect, useRef } from 'react';
import { api } from '../lib/api';
import { useAuth } from './AppContext';
import { mapForm2Submission } from './simulationMappers';

const Form1Context = createContext<any>(null);
const ApplicationContext = createContext<any>(null);
const Form2Context = createContext<any>(null);
const GuidanceContext = createContext<any>(null);
const LogbookContext = createContext<any>(null);
const DefenseContext = createContext<any>(null);
const WorkflowNotificationsContext = createContext<any>(null);

// Status lamaran mitra tetap dipakai mentah (bahasa Inggris) untuk logika,
// tapi tampilannya butuh label Indonesia + warna. Kuncinya harus persis sama
// dengan enum kolom applications.status di database.
// Cuma 'Applied' yang bisa muncul: lamaran mitra tidak menentukan mahasiswa
// diterima di mana. Status tak dikenal jatuh ke fallback, bukan error.
const APPLICATION_STATUS_DISPLAY = {
    Applied: { label: 'Dilamar', color: 'blue' },
};

const EMPTY_STATE = {
    form1Submission: null,
    form2Submissions: [],
    pengajuanPembimbing: null,
    logbookEntries: [],
    logbookPeriod: null,
    sidangSubmission: null,
    sidangSchedule: null,
    activeApplications: [],
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
                        statusLabel: APPLICATION_STATUS_DISPLAY[a.status]?.label ?? a.status,
                        statusColor: APPLICATION_STATUS_DISPLAY[a.status]?.color ?? 'blue',
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
                        lingkupMagang: supervisorRes.value.application.lingkup_magang || '',
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
        await api.upload('/form1', formData);
        await refreshProfile();
        await fetchAllStudentData();
    }, [refreshProfile, fetchAllStudentData]);

    // Konfirmasi hasil magang non-wajib (state AwaitingConfirmation).
    // formData: hasil=diterima (+ company/periode/LoA) atau hasil=ditolak.
    const confirmCycle = useCallback(async (formData) => {
        const res = await api.upload('/student/cycle/confirm', formData);
        await refreshProfile();
        updateStudentLocally({ accessStatus: res.access_status });
        // Panel status baca form1Submission.status, bukan cuma accessStatus --
        // tanpa fetch ulang, panel konfirmasi masih tampil sampai user refresh.
        await fetchAllStudentData();
        return res;
    }, [refreshProfile, updateStudentLocally, fetchAllStudentData]);

    // Reset siklus mandiri: hanya tersedia saat CycleCompleted / ElectiveCompleted.
    // Riwayat magang tetap tersimpan di server (internship_cycles).
    const resetCycle = useCallback(async () => {
        await api.post('/student/cycle/reset');
        await refreshProfile();
        updateStudentLocally({ accessStatus: 'Unverified' });
        setState((s) => ({
            ...s,
            form1Submission: null,
            form2Submissions: [],
            logbookEntries: [],
            sidangSubmission: null,
            sidangSchedule: null,
            pengajuanPembimbing: null,
            activeApplications: [],
        }));
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
            nama_pimpinan:     formData.namaPimpinan,
            jabatan_pimpinan:  formData.jabatanPimpinan,
            alamat_perusahaan: formData.alamatPerusahaan,
            lingkup_magang:    formData.lingkupMagang,
            tanggal_mulai:     formData.tanggalMulai,
            tanggal_selesai:   formData.tanggalSelesai,
        };
        const data = await api.post('/form2', payload);
        setState((s) => ({
            ...s,
            form2Submissions: [mapForm2Submission(data.submission), ...s.form2Submissions],
        }));
    }, []);
    const submitPengajuanPembimbing = useCallback(async (formData) => {
        const fd = new FormData();
        fd.append('company_name', formData.namaPerusahaan);
        fd.append('company_contact', `${formData.namaPraktisi} - ${formData.noTelepon}`);
        fd.append('lingkup_magang', formData.lingkupMagang);
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
                lingkupMagang: formData.lingkupMagang,
                namaPraktisi: formData.namaPraktisi,
                jabatanPraktisi: formData.jabatanPraktisi,
                noTelepon: formData.noTelepon,
                email: formData.email,
                mulaiMagang: formData.mulaiMagang ? new Date(formData.mulaiMagang).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '',
                selesaiMagang: formData.selesaiMagang ? new Date(formData.selesaiMagang).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '',
                submittedAt: new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }),
            },
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

        await api.upload('/defense', fd);
        await refreshProfile();
        updateStudentLocally({ accessStatus: 'AwaitingDefense' });

        setState((s) => ({
            ...s,
            sidangSubmission: { status: 'Pending', submittedAt: new Date().toISOString() },
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
                list.push({
                    message: `Lamaran Anda sebagai ${app.position} di ${app.companyName} berhasil dikirim.`,
                    time: dateStr || 'Baru saja',
                });
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

        if (student?.accessStatus === 'CycleCompleted') {
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
        resetCycle,
        confirmCycle,
    }), [state.form1Submission, submitForm1, resetForm1, resetCycle, confirmCycle]);

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

    return (
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
    );
}

function useRequiredContext(context: React.Context<any>, hookName: string) {
    const ctx = useContext(context);
    if (!ctx) throw new Error(`${hookName} must be used within StudentWorkflowProvider`);
    return ctx;
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
