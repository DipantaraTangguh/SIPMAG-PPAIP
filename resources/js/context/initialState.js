/**
 * context/initialState.js
 * Default state shape for the SimulationContext.
 * Extracted from SimulationContext.jsx for separation of concerns.
 */
export const STORAGE_KEY = 'portal_magang_simulation_state';

export const INITIAL_STATE = {
    isLoggedIn: false,

    student: {
        name: 'Tangguh Dipantara',
        nim: '1234567890',
        programStudi: 'Sistem Informasi',
        email: 'tangguh@student.bakrie.ac.id',
        accessStatus: 'Unverified',
        approvedLogbookCount: 0,
        dpm: null,
    },

    form1Submission: null,
    form2Submissions: [],
    pengajuanPembimbing: null,
    logbookEntries: [],

    sidangSubmission: null,
    sidangSchedule: null,

    activeApplications: [],

    notifications: [
        {
            id: 1,
            message: 'Isi form 1 terlebih dahulu untuk melanjutkan ke proses selanjutnya',
            time: '3 hari yang lalu',
        },
    ],
};
