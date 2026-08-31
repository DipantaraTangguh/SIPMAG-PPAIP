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
 * Harus sama dengan InternshipCycleResetService::RESETTABLE_STATUSES.
 * Server tetap penentunya; daftar ini hanya menentukan kapan tombolnya
 * ditawarkan supaya mahasiswa tidak menekan tombol yang pasti ditolak.
 */
const RESETTABLE_STATUSES = ['CycleCompleted', 'ElectiveCompleted'];

export const canResetCycle = (accessStatus) =>
    RESETTABLE_STATUSES.includes(accessStatus);

const SIDANG_ACCESS_STATUSES = [
    'LogbookComplete',
    'AwaitingDefense',
    'CycleCompleted',
];
export const canAccessSidang = (accessStatus) =>
    SIDANG_ACCESS_STATUSES.includes(accessStatus);
