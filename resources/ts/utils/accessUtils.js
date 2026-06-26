export const PORTAL_ACCESS_STATUSES = [
    'ApprovedForm1',
    'HasApplication',
    'HasDPM',
    'LogbookComplete',
    'MenungguSidang',
    'SiklusSelesai',
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
];

export const SECURED_INTERNSHIP_MESSAGE =
    'Pengajuan magang Anda sudah disetujui. Anda sudah dapat menjalani magang sehingga tidak dapat melamar lowongan mitra atau mengajukan Form 2 lain.';

export const hasAcceptedPartnerApplication = (applications = []) =>
    applications.some((application) => application.status === 'Accepted');

export const hasApprovedForm2Submission = (submissions = []) =>
    submissions.some((submission) =>
        ['ApprovedForm2', 'Disetujui'].includes(submission.status)
    );

export const hasSecuredInternship = (
    accessStatus,
    applications = [],
    form2Submissions = [],
) =>
    SECURED_INTERNSHIP_STATUSES.includes(accessStatus) ||
    hasAcceptedPartnerApplication(applications) ||
    hasApprovedForm2Submission(form2Submissions);

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
    accessStatus === 'SiklusSelesai';
