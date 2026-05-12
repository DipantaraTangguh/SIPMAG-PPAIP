/**
 * SimulationPanel.jsx
 * Floating dev panel (bottom-right) that lets the demo operator
 * trigger state changes without a real backend.
 * Thin orchestrator — composes extracted panel sections.
 */
import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useSimulation } from '../context/SimulationContext';
import { ChevronDown, ChevronUp } from 'lucide-react';
import { STATUS_BADGE_COLORS } from '../constants/simulationConstants';
import AuthControls from './simulation/panels/AuthControls';
import Form1Controls from './simulation/panels/Form1Controls';
import Form2Controls from './simulation/panels/Form2Controls';
import PengajuanControls from './simulation/panels/PengajuanControls';
import LogbookControls from './simulation/panels/LogbookControls';
import SidangControls from './simulation/panels/SidangControls';
import QuickNavigation from './simulation/panels/QuickNavigation';

export default function SimulationPanel() {
    const sim = useSimulation();
    const navigate = useNavigate();
    const [open, setOpen] = useState(false);

    const status = sim.student?.accessStatus || 'Unverified';

    return (
        <div className="fixed bottom-4 right-4 z-[9999] w-[240px] select-none">
            {/* Header toggle */}
            <button
                type="button"
                onClick={() => setOpen((v) => !v)}
                className="flex w-full items-center justify-between rounded-t-xl bg-gray-800 px-4 py-2.5 text-left text-[13px] font-bold text-white transition-colors hover:bg-gray-700"
                style={!open ? { borderRadius: '12px' } : undefined}
            >
                <span>🎮 Simulation Panel</span>
                {open ? <ChevronDown className="h-4 w-4" /> : <ChevronUp className="h-4 w-4" />}
            </button>

            {open && (
                <div className="flex max-h-[70vh] flex-col gap-3 overflow-y-auto rounded-b-xl bg-gray-900 px-4 pb-4 pt-3">
                    {/* Current state */}
                    <div>
                        <p className="mb-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-500">
                            Status
                        </p>
                        <div className="flex flex-wrap gap-1.5">
                            <span className={`rounded px-2 py-0.5 text-[10px] font-bold text-white ${sim.isLoggedIn ? 'bg-green-600' : 'bg-gray-600'}`}>
                                {sim.isLoggedIn ? '🔓 Login' : '🔒 Logged Out'}
                            </span>
                            <span className={`rounded px-2 py-0.5 text-[10px] font-bold text-white ${STATUS_BADGE_COLORS[status] || 'bg-gray-600'}`}>
                                {status}
                            </span>
                        </div>
                        {(['HasDPM', 'LogbookComplete', 'MenungguSidang', 'SiklusSelesai'].includes(status)) && sim.student?.dpm && (
                            <p className="mt-1 text-[10px] italic text-purple-400">
                                DPM: {sim.student.dpm.name?.substring(0, 18)}...
                            </p>
                        )}
                    </div>

                    <AuthControls sim={sim} navigate={navigate} />
                    <Form1Controls sim={sim} status={status} />
                    <Form2Controls sim={sim} />
                    <PengajuanControls sim={sim} status={status} />
                    <LogbookControls sim={sim} status={status} />
                    <SidangControls sim={sim} status={status} />
                    <QuickNavigation navigate={navigate} />
                </div>
            )}
        </div>
    );
}
