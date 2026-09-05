const PORTAL_ACCESS_STATUSES = [
    'ApprovedForm1',
    'HasApplication',
    'HasDPM',
    'LogbookComplete',
    'AwaitingDefense',
    'CycleCompleted',
    'ElectiveCompleted',
    'AwaitingConfirmation',
];
export const canAccessPortal = (accessStatus) =>
    PORTAL_ACCESS_STATUSES.includes(accessStatus);

const SECURED_INTERNSHIP_STATUSES = [
    'HasDPM',
    'LogbookComplete',
    'AwaitingDefense',
    'CycleCompleted',
    'ElectiveCompleted',
];

export const SECURED_INTERNSHIP_MESSAGE =
    'DPM Anda sudah ditunjuk atau pengajuan DPM sudah disetujui, sehingga Anda tidak dapat melamar lowongan mitra lagi.';

export const FORM2_LOCKED_MESSAGE =
    'DPM Anda sudah ditunjuk atau pengajuan DPM sudah disetujui, sehingga Form 2 tidak dapat diajukan lagi.';

export const hasSecuredInternship = (accessStatus) =>
    SECURED_INTERNSHIP_STATUSES.includes(accessStatus);

/**
 * Tahap bimbingan terbuka sejak mahasiswa mengajukan pembimbing sampai
 * siklusnya ditutup. Didefinisikan di sini, bukan ditulis tangan di halaman,
 * supaya tidak melenceng dari daftar status lain saat alurnya berubah.
 */
const GUIDANCE_ACCESS_STATUSES = [
    'HasApplication',
    'HasDPM',
    'LogbookComplete',
    'AwaitingDefense',
    'CycleCompleted',
];

export const canAccessGuidance = (accessStatus) =>
    GUIDANCE_ACCESS_STATUSES.includes(accessStatus);

const SIDANG_ACCESS_STATUSES = [
    'LogbookComplete',
    'AwaitingDefense',
    'CycleCompleted',
];
export const canAccessSidang = (accessStatus) =>
    SIDANG_ACCESS_STATUSES.includes(accessStatus);
