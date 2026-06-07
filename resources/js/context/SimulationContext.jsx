import React, { createContext, useContext, useState, useMemo, useCallback, useEffect } from 'react';
import { api } from '../lib/api';

const SimulationContext = createContext(null);

const EMPTY_STATE = {
    isLoggedIn: false,
    isLoading: true,
    student: null,
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

export function SimulationProvider({ children }) {
    const [state, setState] = useState(EMPTY_STATE);
    useEffect(() => {
        const boot = async () => {
            try {
                const { user } = await api.get('/me');
                setState((s) => ({
                    ...s,
                    isLoggedIn: true,
                    isLoading: false,
                    student: mapStudent(user),
                }));
                // Ambil modul barengan biar dashboard nggak nunggu satu-satu.
                if (user.role === 'mahasiswa') {
                    fetchAllStudentData();
                }
            } catch {
                setState((s) => ({ ...s, isLoading: false }));
            }
        };
        boot();
    }, []);
    function mapStudent(user) {
        if (!user || !user.student) return null;
        const s = user.student;
        return {
            name: s.name,
            nim: s.nim,
            programStudi: s.study_program,
            email: s.email,
            semester: s.semester,
            tahunAkademik: s.tahun_akademik,
            jumlahSks: s.jumlah_sks,
            ipk: s.ipk,
            accessStatus: s.access_status,
            approvedLogbookCount: s.approved_logbook_count,
            dpm: s.dpm ? {
                name: s.dpm.name,
                nidn: s.dpm.nidn,
                email: s.dpm.contact,
                initials: s.dpm.name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase(),
            } : null,
        };
    }
    function mapForm2Submission(s) {
        if (!s) return s;
        const statusMap = {
            PendingReview:  'Menunggu Review',
            ApprovedForm2:  'Disetujui',
            RejectedForm2:  'Ditolak',
        };
        const submittedAt = s.submitted_at
            ? new Date(s.submitted_at).toLocaleDateString('id-ID', {
                day: 'numeric', month: 'short', year: 'numeric',
            })
            : null;
        return {
            id: s.id,
            companyName: s.company_name,
            position: s.lingkup_magang,
            alamatPerusahaan: s.alamat_perusahaan,
            tanggalMulai: s.tanggal_mulai,
            tanggalSelesai: s.tanggal_selesai,
            status: statusMap[s.status] || s.status,
            submittedAt,
            rejectionReason: s.rejection_reason,
            pdfPath: s.pdf_path,
        };
    }
    const refreshProfile = useCallback(async () => {
        try {
            const { user } = await api.get('/me');
            setState((s) => ({
                ...s,
                student: mapStudent(user),
            }));
        } catch { /* aman di-skip */ }
    }, []);
    const fetchAllStudentData = useCallback(async () => {
        try {
            const [form1Res, appsRes, form2Res, logbookRes, sidangRes, supervisorRes] = await Promise.allSettled([
                api.get('/form1'),
                api.get('/applications'),
                api.get('/form2'),
                api.get('/logbooks'),
                api.get('/defense'),
                api.get('/supervisor-application'),
            ]);

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
                    ? (appsRes.value.applications || []).map(a => ({
                        id: a.id,
                        vacancyId: a.internship_id,
                        companyName: a.internship?.company_name,
                        position: a.internship?.position,
                        status: a.status,
                        appliedAt: a.created_at,
                    }))
                    : [],
                form2Submissions: form2Res.status === 'fulfilled'
                    ? (form2Res.value.submissions || []).map(mapForm2Submission)
                    : [],
                logbookEntries: logbookRes.status === 'fulfilled'
                    ? (logbookRes.value.logbooks || []).map(l => ({
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
    const login = useCallback(async (loginId, password) => {
        try {
            const data = await api.login(loginId, password);
            const student = mapStudent(data.user);
            setState((s) => ({
                ...s,
                isLoggedIn: true,
                student,
            }));
            // Setelah login, refresh semua state utama.
            if (data.user.role === 'mahasiswa') {
                setTimeout(() => fetchAllStudentData(), 100);
            }
            return { success: true };
        } catch (err) {
            return { success: false, error: err.message };
        }
    }, [fetchAllStudentData]);

    const logout = useCallback(async () => {
        await api.logout();
        setState(EMPTY_STATE);
        setState((s) => ({ ...s, isLoading: false }));
    }, []);
    const submitForm1 = useCallback(async (formData) => {
        try {
            await api.upload('/form1', formData);
            await refreshProfile();
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
                student: { ...s.student, accessStatus: 'PendingReview' },
                notifications: [
                    { id: Date.now(), message: 'Form 1 berhasil diajukan. Menunggu persetujuan Kaprodi.', time: 'Baru saja' },
                    ...s.notifications,
                ],
            }));
        } catch (err) {
            throw err;
        }
    }, [refreshProfile]);

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

        setState((s) => ({
            ...s,
            student: { ...s.student, accessStatus: 'HasApplication' },
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
    }, [refreshProfile]);
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

        setState((s) => ({
            ...s,
            student: { ...s.student, accessStatus: 'MenungguSidang' },
            sidangSubmission: { status: 'Pending', submittedAt: new Date().toISOString() },
            notifications: [
                { id: Date.now(), message: 'Dokumen sidang berhasil dikirim.', time: 'Baru saja' },
                ...s.notifications,
            ],
        }));
    }, [refreshProfile]);

    const notifications = useMemo(() => {
        const list = [];

        // Form 1 jadi sumber status akademik awal.
        if (state.form1Submission) {
            const dateStr = state.form1Submission.submittedAt ? ` pada ${state.form1Submission.submittedAt}` : '';
            if (state.student?.accessStatus === 'PendingReview') {
                list.push({
                    message: `Form 1 Anda berhasil diajukan${dateStr}. Menunggu persetujuan Kaprodi.`,
                    time: state.form1Submission.submittedAt || 'Baru saja',
                });
            } else if (state.student?.accessStatus === 'RejectedForm1') {
                list.push({
                    message: `Form 1 Anda ditolak/perlu direvisi oleh Kaprodi${state.form1Submission.rejectionReason ? `: "${state.form1Submission.rejectionReason}"` : '.'}`,
                    time: 'Baru saja',
                });
            } else if (
                state.student?.accessStatus !== 'Unverified' &&
                state.student?.accessStatus !== 'RejectedForm1' &&
                state.student?.accessStatus !== 'PendingReview'
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
            if (state.student?.dpm) {
                list.push({
                    message: `Dosen Pembimbing Magang (DPM) Anda telah ditetapkan: ${state.student.dpm.name}.`,
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

        if (state.student?.accessStatus === 'SiklusSelesai') {
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
        state.student
    ]);
    const value = useMemo(() => ({
        ...state,
        notifications,
        login,
        logout,
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
        state, notifications, login, logout, submitForm1, resetForm1,
        applyToVacancy, submitForm2, submitPengajuanPembimbing,
        addLogbookEntry, updateLogbookEntry, submitSidang,
        refreshProfile, fetchAllStudentData,
    ]);

    return (
        <SimulationContext.Provider value={value}>
            {children}
        </SimulationContext.Provider>
    );
}

export function useSimulation() {
    const ctx = useContext(SimulationContext);
    if (!ctx) throw new Error('useSimulation must be used within SimulationProvider');
    return ctx;
}
