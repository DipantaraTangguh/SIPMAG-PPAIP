/**
 * SimulationContext.jsx
 * Single source of truth for the SIPMAG frontend-only simulation.
 * Thin orchestrator — imports initial state and action hooks from modules.
 *
 * SIPMAG Student Access Status Lifecycle
 * ========================================
 * Unverified       → Account created, no Form 1
 * PendingReview    → Form 1 submitted, awaiting Kaprodi
 * RejectedForm1    → Form 1 rejected, must resubmit
 * ApprovedForm1    → Form 1 approved, can apply
 * HasApplication   → Applied (Mitra) OR
 *                    Form 2 approved (Mandiri)
 * HasDPM           → Pengajuan Pembimbing
 *                    submitted + DPM assigned
 * LogbookComplete  → 6 logbook entries approved
 * MenungguSidang   → Sidang documents submitted,
 *                    awaiting PPAIP review
 * SiklusSelesai    → Sidang completed, full
 *                    cycle done (terminal state)
 *
 * Cycle reset: resetFullCycle() → Unverified
 */
import React, { createContext, useContext, useState, useMemo, useEffect } from 'react';
import { INITIAL_STATE, STORAGE_KEY } from './initialState';
import { useAuthActions } from './actions/authActions';
import { useForm1Actions } from './actions/form1Actions';
import { useForm2Actions } from './actions/form2Actions';
import { useApplicationActions } from './actions/applicationActions';
import { usePembimbingActions } from './actions/pembimbingActions';
import { useLogbookActions } from './actions/logbookActions';
import { useSidangActions } from './actions/sidangActions';

const SimulationContext = createContext(null);

function loadPersistedState() {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        if (raw) return JSON.parse(raw);
    } catch { /* ignore corrupt data */ }
    return INITIAL_STATE;
}

export function SimulationProvider({ children }) {
    const [state, setState] = useState(loadPersistedState);

    // Persist to localStorage on every state change
    useEffect(() => {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
        } catch { /* storage full — ignore */ }
    }, [state]);

    // Compose all action modules
    const { login, logout } = useAuthActions(setState);
    const { submitForm1, simulateApproveForm1, simulateRejectForm1, resetForm1 } = useForm1Actions(setState);
    const { submitForm2, simulateApproveForm2, simulateRejectForm2 } = useForm2Actions(setState);
    const { applyToVacancy } = useApplicationActions(setState);
    const { submitPengajuanPembimbing, assignDPM } = usePembimbingActions(setState);
    const { addLogbookEntry, updateLogbookEntry, approveLogbookEntry, rejectLogbookEntry, completeSixLogbooks } = useLogbookActions(setState);
    const { submitSidang, assignSidangSchedule, completeSidangCycle, resetFullCycle } = useSidangActions(setState);

    const value = useMemo(
        () => ({
            ...state,
            login,
            logout,
            submitForm1,
            simulateApproveForm1,
            simulateRejectForm1,
            resetForm1,
            applyToVacancy,
            submitForm2,
            simulateApproveForm2,
            simulateRejectForm2,
            submitPengajuanPembimbing,
            assignDPM,
            addLogbookEntry,
            updateLogbookEntry,
            approveLogbookEntry,
            rejectLogbookEntry,
            completeSixLogbooks,
            submitSidang,
            assignSidangSchedule,
            completeSidangCycle,
            resetFullCycle,
        }),
        [
            state,
            login,
            logout,
            submitForm1,
            simulateApproveForm1,
            simulateRejectForm1,
            resetForm1,
            applyToVacancy,
            submitForm2,
            simulateApproveForm2,
            simulateRejectForm2,
            submitPengajuanPembimbing,
            assignDPM,
            addLogbookEntry,
            updateLogbookEntry,
            approveLogbookEntry,
            rejectLogbookEntry,
            completeSixLogbooks,
            submitSidang,
            assignSidangSchedule,
            completeSidangCycle,
            resetFullCycle,
        ],
    );

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
