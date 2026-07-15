export const PORTAL_ACCESS_STATUSES = [
    'ApprovedForm1',
    'HasApplication',
    'HasDPM',
    'LogbookComplete',
    'MenungguSidang',
    'SiklusSelesai',
    'SelesaiNonWajib',
    'MenungguKonfirmasi',
];
export const canAccessPortal = (accessStatus) =>
    PORTAL_ACCESS_STATUSES.includes(accessStatus);
export const canSubmitForm2 = (accessStatus) =>
    PORTAL_ACCESS_STATUSES.includes(accessStatus);

export const SECURED_INTERNSHIP_STATUSES = [
    'HasDPM',
    'LogbookComplete',
    'MenungguSidang',
    'SiklusSelesai',
    'SelesaiNonWajib',
    'MenungguKonfirmasi',
];

export const SECURED_INTERNSHIP_MESSAGE =
    'DPM Anda sudah ditunjuk atau pengajuan DPM sudah disetujui, sehingga Anda tidak dapat melamar lowongan mitra lagi.';

export const FORM2_LOCKED_MESSAGE =
    'DPM Anda sudah ditunjuk atau pengajuan DPM sudah disetujui, sehingga Form 2 tidak dapat diajukan lagi.';

export const hasSecuredInternship = (accessStatus) =>
    SECURED_INTERNSHIP_STATUSES.includes(accessStatus);

export const LOGBOOK_ACCESS_STATUSES = [
    'HasDPM',
    'LogbookComplete',
    'MenungguSidang',
    'SiklusSelesai',
];
export const SIDANG_ACCESS_STATUSES = [
    'LogbookComplete',
    'MenungguSidang',
    'SiklusSelesai',
];
export const canAccessSidang = (accessStatus) =>
    SIDANG_ACCESS_STATUSES.includes(accessStatus);
export const isCycleComplete = (accessStatus) =>
    accessStatus === 'SiklusSelesai' || accessStatus === 'SelesaiNonWajib';
