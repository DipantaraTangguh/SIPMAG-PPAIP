/**
 * constants/statusConstants.js
 * All accessStatus values as named constants.
 * Prevents typo bugs from raw string comparisons.
 */
export const ACCESS_STATUS = {
    UNVERIFIED: 'Unverified',
    PENDING_REVIEW: 'PendingReview',
    APPROVED_FORM1: 'ApprovedForm1',
    REJECTED_FORM1: 'RejectedForm1',
    HAS_APPLICATION: 'HasApplication',
    HAS_DPM: 'HasDPM',
    LOGBOOK_COMPLETE: 'LogbookComplete',
    MENUNGGU_SIDANG: 'MenungguSidang',
    SIKLUS_SELESAI: 'SiklusSelesai',
};

export const LOGBOOK_STATUS = {
    MENUNGGU: 'Menunggu Review',
    DISETUJUI: 'Disetujui',
    DITOLAK: 'Ditolak',
};

export const APPLICATION_STATUS = {
    DILAMAR: 'Dilamar',
    DITERIMA: 'Diterima',
    DITOLAK: 'Ditolak',
};

export const FORM2_STATUS = {
    MENUNGGU: 'Menunggu Review',
    DISETUJUI: 'Disetujui',
    DITOLAK: 'Ditolak',
};
