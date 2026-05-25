/**
 * MandiriTabContent.jsx
 * The main container for the Mandiri tab content inside Portal Magang.
 * Switches between EmptyState and FilledState based on form2Submissions.
 * Gates access behind Form 1 approval.
 */
import React from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { useSimulation } from '../../../context/SimulationContext';
import { Info } from 'lucide-react';
import MandiriInfoBanner from './MandiriInfoBanner';
import MandiriSectionHeader from './MandiriSectionHeader';
import MandiriEmptyState from './MandiriEmptyState';
import MandiriSidePanel from './MandiriSidePanel';
import MandiriFilledState from './MandiriFilledState';
import { canAccessPortal } from '../../../utils/accessUtils';

export default function MandiriTabContent() {
    const navigate = useNavigate();
    const location = useLocation();
    const { form2Submissions, student } = useSimulation();

    const submissions = form2Submissions || [];
    const accessStatus = student?.accessStatus;

    // Form 2 is only accessible if Form 1 is approved (or beyond)
    const isUnlocked = canAccessPortal(accessStatus); // FIXED

    // Show warning if blocked by navigation redirect OR by status
    const showWarning = !isUnlocked || location.state?.blockedReason === 'form1_required';

    const handleCreateNew = () => {
        navigate('/portal/mandiri/form2/new');
    };

    return (
        <div className="animate-in fade-in slide-in-from-bottom-4 duration-500">
            <MandiriInfoBanner />

            {/* Form 1 gate warning banner */}
            {showWarning && (
                <div className="my-6 flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                    <Info className="h-[18px] w-[18px] flex-shrink-0 text-amber-500" />
                    <p className="flex-1 text-[13px] leading-relaxed text-amber-700">
                        Anda belum dapat mengajukan Form 2. Selesaikan dan tunggu
                        persetujuan Form 1 terlebih dahulu sebelum mengajukan surat
                        pengantar magang.
                    </p>
                    <button
                        type="button"
                        onClick={() => navigate('/form1')}
                        className="flex-shrink-0 rounded-lg bg-primary px-3.5 py-1.5 text-xs font-bold text-white hover:bg-primary-hover"
                    >
                        Isi Form 1 →
                    </button>
                </div>
            )}

            {submissions.length === 0 ? (
                <>
                    <MandiriSectionHeader count={0} />
                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_320px]">
                        <div>
                            <MandiriEmptyState
                                onCreateNew={handleCreateNew}
                                isLocked={!isUnlocked}
                            />
                        </div>
                        <MandiriSidePanel />
                    </div>
                </>
            ) : (
                <div className="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_320px]">
                    <div>
                        <MandiriFilledState
                            submissions={submissions}
                            onCreateNew={handleCreateNew}
                        />
                    </div>
                    <div className="max-lg:mt-2 lg:mt-[86px]">
                        <MandiriSidePanel />
                    </div>
                </div>
            )}
        </div>
    );
}
