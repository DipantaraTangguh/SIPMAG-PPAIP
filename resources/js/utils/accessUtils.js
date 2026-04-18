/**
 * utils/accessUtils.js
 * Definitions and helpers for checking access to different parts
 * of the application based on student.accessStatus.
 */

/**
 * Status values that grant full portal access.
 * Students with these statuses have completed
 * Form 1 and are eligible to browse and apply for internships.
 */
export const PORTAL_ACCESS_STATUSES = [
    'ApprovedForm1',
    'HasApplication',
    'HasDPM',
    'LogbookComplete',
    'MenungguSidang',
    'SiklusSelesai',
];

/**
 * Check if student can access the internship portal,
 * browse vacancies, and apply for internships.
 * @param {string} accessStatus
 * @returns {boolean}
 */
export const canAccessPortal = (accessStatus) =>
    PORTAL_ACCESS_STATUSES.includes(accessStatus);

/**
 * Check if student can submit Form 2 (Mandiri track).
 * Same condition as portal access.
 * @param {string} accessStatus
 * @returns {boolean}
 */
export const canSubmitForm2 = (accessStatus) =>
    PORTAL_ACCESS_STATUSES.includes(accessStatus);

/**
 * Status values that grant Logbook/Bimbingan access.
 * Students must have a DPM assigned.
 */
export const LOGBOOK_ACCESS_STATUSES = [
    'HasDPM',
    'LogbookComplete',
    'MenungguSidang',
    'SiklusSelesai',
];

/**
 * Status values that grant Sidang access.
 * Students must have completed 6 logbook entries.
 */
export const SIDANG_ACCESS_STATUSES = [
    'LogbookComplete',
    'MenungguSidang',
    'SiklusSelesai',
];

/**
 * Check if student can access the Sidang module.
 * @param {string} accessStatus
 * @returns {boolean}
 */
export const canAccessSidang = (accessStatus) =>
    SIDANG_ACCESS_STATUSES.includes(accessStatus);

/**
 * Check if the internship cycle is fully complete.
 * @param {string} accessStatus
 * @returns {boolean}
 */
export const isCycleComplete = (accessStatus) =>
    accessStatus === 'SiklusSelesai';
