/**
 * context/SimulationContext.jsx
 * Single source of truth for the Portal Magang application state.
 *
 * All data is fetched from the real Laravel API via lib/api.js.
 * Consumer components destructure { student, login, logout, submitForm1, ... }
 * from useSimulation().
 */
import React, { createContext, useContext, useState, useMemo, useCallback, useEffect } from 'react';
import { api, getToken, clearToken } from '../lib/api';

const SimulationContext = createContext(null);

const EMPTY_STATE = {
    isLoggedIn: false,
    isLoading: true,
    student: null,
    form1Submission: null,
    form2Submissions: [],
    pengajuanPembimbing: null,
    logbookEntries: [],
    sidangSubmission: null,
    sidangSchedule: null,
    activeApplications: [],
    notifications: [],
};

export function SimulationProvider({ children }) {
    const [state, setState] = useState(EMPTY_STATE);

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Boot: check if a token exists and fetch /me
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    useEffect(() => {
        const boot = async () => {
            const token = getToken();
            if (!token) {
                setState((s) => ({ ...s, isLoading: false }));
                return;
            }
            try {
                const { user } = await api.get('/me');
                setState((s) => ({
                    ...s,
                    isLoggedIn: true,
                    isLoading: false,
                    student: mapStudent(user),
                }));
                // Fetch module data in parallel
                if (user.role === 'mahasiswa') {
                    fetchAllStudentData();
                }
            } catch {
                clearToken();
                setState((s) => ({ ...s, isLoading: false }));
            }
        };
        boot();
    }, []);

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Helpers
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

    /** Map API user response to the state shape components expect */
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

    /** Map API Form2Submission → shape expected by MandiriFilledState cards */
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

    /** Refresh student profile from /me */
    const refreshProfile = useCallback(async () => {
        try {
            const { user } = await api.get('/me');
            setState((s) => ({
                ...s,
                student: mapStudent(user),
            }));
        } catch { /* ignore */ }
    }, []);

    /** Fetch all module data for mahasiswa after login */
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
                        status: l.status,
                        dpmNote: l.dpm_note,
                    }))
                    : [],
                sidangSubmission: sidangRes.status === 'fulfilled'
                    ? sidangRes.value.submission
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
        } catch { /* ignore partial failures */ }
    }, []);

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Auth Actions
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

    const login = useCallback(async (loginId, password) => {
        try {
            const data = await api.login(loginId, password);
            const student = mapStudent(data.user);
            setState((s) => ({
                ...s,
                isLoggedIn: true,
                student,
            }));
            // Fetch all data after login
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

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Form 1 Actions
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

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
                    approver: form1Res.approver || null,
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

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Application Actions (Portal Mitra)
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

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

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Form 2 Actions (Mandiri)
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

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

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Pembimbing Actions
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

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

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Logbook Actions
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

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
            status: data.logbook.status,
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
                    status: 'PendingReview',
                    dpmNote: null,
                } : e
            ),
        }));
    }, []);

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Defense (Sidang) Actions
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

    const submitSidang = useCallback(async (files) => {
        const fd = new FormData();
        // Map frontend keys → backend field names
        fd.append('laporan', files.laporanAkhir || files.laporan);
        fd.append('poster', files.posterPresentasi || files.poster);
        fd.append('krs', files.krsMataKuliah || files.krs);

        await api.upload('/defense', fd);
        await refreshProfile();

        setState((s) => ({
            ...s,
            student: { ...s.student, accessStatus: 'MenungguSidang' },
            sidangSubmission: { submittedAt: new Date().toISOString() },
            notifications: [
                { id: Date.now(), message: 'Dokumen sidang berhasil dikirim.', time: 'Baru saja' },
                ...s.notifications,
            ],
        }));
    }, [refreshProfile]);

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Context Value
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

    const value = useMemo(() => ({
        ...state,
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
        // Utility: force refresh from API
        refreshProfile,
        fetchAllStudentData,
    }), [
        state, login, logout, submitForm1, resetForm1,
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
