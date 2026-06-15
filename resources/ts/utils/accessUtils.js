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
